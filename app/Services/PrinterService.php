<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Tenant;
use Exception;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Component\Process\Process;

class PrinterService
{
    protected $printer;

    protected $connector;

    protected ?string $lastError = null;

    /**
     * Print a receipt for an order
     */
    public function printOrder(Order $order, ?string $additionalFooter = null): bool
    {
        try {
            $order->load(['items.product.category', 'user', 'tenant', 'shift']);
            $this->initialize($order->tenant);

            if (! $this->printer) {
                return false;
            }

            /* Header - Tenant Identity */
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);

            // Logo
            $logoPath = null;
            if ($order->tenant->logo) {
                $logoPath = storage_path('app/public/'.$order->tenant->logo);
            }
            if ($logoPath && file_exists($logoPath)) {
                try {
                    $logo = EscposImage::load($logoPath, false);
                    $this->printer->bitImage($logo);
                } catch (Exception $e) {
                }
            }

            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->text($order->tenant->name."\n");
            $this->printer->selectPrintMode();

            if ($order->tenant->address) {
                $this->printer->text($order->tenant->address."\n");
            }
            if ($order->tenant->phone) {
                $this->printer->text($order->tenant->phone."\n");
            }
            $this->printer->text("--------------------------------\n");

            /* Transaction Identity Section */
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text($this->formatTwoColumns('Bill No', ': '.$order->order_number, 32)."\n");
            $this->printer->text($this->formatTwoColumns('Name', ': '.($order->customer_name ?? '-'), 32)."\n");

            // Date & Shift on same line
            $dateStr = 'Date   : '.$order->created_at->format('d/m/Y');
            $shiftStr = 'Shift : '.($order->shift->name ?? ($order->shift_id ? 'Shift #'.$order->shift_id : 'NIGHT'));
            $this->printer->text($this->formatTwoColumns($dateStr, $shiftStr, 32)."\n");

            // Table No & Cashier on same line
            $tableStr = 'Table No: '.($order->table_number ?? '-');
            $cashierStr = 'Cashier: '.($order->user->name ?? 'User');
            $this->printer->text($this->formatTwoColumns($tableStr, $cashierStr, 32)."\n");

            $this->printer->text("--------------------------------\n");

            /* Table Header */
            $this->printer->text("QTY  DESCRIPTION  DISC(%)  TOTAL\n");
            $this->printer->text("--------------------------------\n");

            /* Items Grouped by Category */
            $itemsByCategory = $order->items->groupBy(function ($item) {
                return strtoupper($item->product->category->name ?? 'OTH');
            });

            foreach ($itemsByCategory as $categoryName => $items) {
                $this->printer->text($categoryName."\n");
                $categorySubtotal = 0;

                foreach ($items as $item) {
                    $qty = str_pad($item->quantity, 3);
                    $name = substr($item->product_name, 0, 16);
                    $disc = str_pad(number_format($item->discount_amount > 0 ? ($item->discount_amount / ($item->price * $item->quantity) * 100) : 0, 0), 2, ' ', STR_PAD_LEFT);
                    $total = number_format($item->subtotal, 0, ',', '.');

                    // QTY  NAME  DISC  TOTAL
                    $line = $qty.' '.str_pad($name, 16).' '.$disc.'% '.str_pad($total, 7, ' ', STR_PAD_LEFT);
                    $this->printer->text($line."\n");

                    $categorySubtotal += $item->subtotal;
                }

                // Category Total line
                $catTotalLabel = '     TOTAL '.$categoryName;
                $catTotalValue = number_format($categorySubtotal, 0, ',', '.');
                $this->printer->text($this->formatTwoColumns($catTotalLabel, $catTotalValue, 32)."\n");
                $this->printer->text("--------------------------------\n");
            }

            /* Footer Summary */
            $this->printer->text($this->formatTwoColumns('SUB TOTAL', number_format($order->subtotal, 0, ',', '.'), 32)."\n");
            $this->printer->text($this->formatTwoColumns('SERVICE', number_format($order->service_charge, 0, ',', '.'), 32)."\n");
            $this->printer->text($this->formatTwoColumns('TAX', number_format($order->tax_amount, 0, ',', '.'), 32)."\n");
            $this->printer->text($this->formatTwoColumns('ROUNDING', number_format($order->rounding, 0, ',', '.'), 32)."\n");

            $this->printer->text("\n");
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->text($this->formatTwoColumns('Grand Total', number_format($order->total_amount, 0, ',', '.'), 32)."\n");
            $this->printer->selectPrintMode();

            $this->printer->text("--------------------------------\n");
            $this->printer->text($this->formatTwoColumns('Total Paid', number_format($order->paid_amount, 0, ',', '.'), 32)."\n");
            $this->printer->text($this->formatTwoColumns('Change', number_format($order->change_amount, 0, ',', '.'), 32)."\n");

            /* Footer Message */
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("\n");

            if ($order->tenant->footer_text) {
                $this->printer->text($order->tenant->footer_text."\n");
            } else {
                $this->printer->text("Terima Kasih!\n");
                $this->printer->text("Selamat Menikmati Hidangan Kami\n");
            }

            if ($additionalFooter) {
                $this->printer->text($additionalFooter."\n");
            }

            $this->printer->text("\n\n");

            /* Cut and close */
            $this->printer->cut();
            $this->printer->close();

            return true;
        } catch (Exception $e) {
            \Log::error('Printing failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Print a kitchen receipt for an order
     */
    public function printKitchenReceipt(Order $order): bool
    {
        try {
            $order->load(['items', 'tenant']);
            $this->initialize($order->tenant);
            if (! $this->printer) {
                return false;
            }

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
            $this->printer->text("DAPUR / KITCHEN\n");
            $this->printer->selectPrintMode();
            $this->printer->text("--------------------------------\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text('No: '.$order->order_number."\n");
            $this->printer->text('Meja: '.($order->table_number ?? '-')."\n");
            $this->printer->text('Waktu: '.$order->created_at->format('H:i:s')."\n");
            $this->printer->text("--------------------------------\n");

            /* Items List for Kitchen */
            foreach ($order->items as $item) {
                // Large font for Quantity and Name
                $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                $this->printer->text($item->quantity.' x '.$item->product_name."\n");
                $this->printer->selectPrintMode();

                // Show notes if any
                if ($item->notes) {
                    $this->printer->setEmphasis(true);
                    $this->printer->text('   NOTE: '.$item->notes."\n");
                    $this->printer->setEmphasis(false);
                }
                $this->printer->text("\n");
            }

            $this->printer->text("--------------------------------\n");
            $this->printer->cut();
            $this->printer->close();

            return true;
        } catch (Exception $e) {
            \Log::error('Kitchen printing failed: '.$e->getMessage());

            return false;
        }
    }

    public function printTestPage(Tenant $tenant, ?string $userName = null): bool
    {
        try {
            $this->lastError = null;
            $settings = $this->resolvePrinterSettings($tenant);
            $this->initialize($tenant);

            if (! $this->printer) {
                $this->lastError ??= 'Printer tidak dapat diinisialisasi.';

                return false;
            }

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $this->printer->text("TEST PRINTER\n");
            $this->printer->selectPrintMode();
            $this->printer->text(($tenant->name ?? 'Restoku')."\n");
            $this->printer->text("--------------------------------\n");
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Tipe    : ".$settings['connection_type']."\n");
            $this->printer->text("Alamat  : ".$settings['address']."\n");

            if ($settings['connection_type'] === 'network') {
                $this->printer->text("Port    : ".$settings['port']."\n");
            }

            $this->printer->text("Waktu   : ".now()->format('d/m/Y H:i:s')."\n");
            $this->printer->text("User    : ".($userName ?: 'System')."\n");
            $this->printer->text("--------------------------------\n");
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Jika struk ini keluar,\nkonfigurasi printer sudah benar.\n\n");
            $this->printer->cut();
            $this->printer->close();

            return true;
        } catch (Exception $e) {
            $this->lastError = $this->formatPrinterExceptionMessage($e);
            \Log::error('Test printing failed: '.$e->getMessage());

            return false;
        }
    }

    public function testWindowsPrinterAccess(Tenant $tenant): array
    {
        $settings = $this->resolvePrinterSettings($tenant);

        if ($settings['connection_type'] !== 'windows') {
            return [
                'ok' => true,
                'message' => 'Bukan printer Windows share.',
                'target' => $settings['address'],
            ];
        }

        $target = $this->resolveWindowsPrinterTarget($settings['address']);

        $testFile = storage_path('app/printer-access-test.txt');
        file_put_contents($testFile, "RESTOKU PHP ACCESS TEST\r\n\f");
        $copyError = null;
        set_error_handler(function (int $severity, string $message) use (&$copyError): bool {
            $copyError = $message;

            return true;
        });

        try {
            if (copy($testFile, $target)) {
                return [
                    'ok' => true,
                    'message' => 'PHP berhasil mengirim file test ke printer share.',
                    'target' => $target,
                ];
            }

            return [
                'ok' => false,
                'message' => $copyError ?? 'PHP gagal mengirim file test ke printer share.',
                'target' => $target,
            ];
        } finally {
            restore_error_handler();
            @unlink($testFile);
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function scanWindowsReadyPrinters(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        $scripts = [
            <<<'POWERSHELL'
$printers = Get-CimInstance Win32_Printer | Where-Object {
    $_.Name -and
    -not $_.WorkOffline -and
    ($_.PrinterStatus -eq 3 -or $_.PrinterStatus -eq 0 -or $_.ExtendedPrinterStatus -eq 2 -or $_.ExtendedPrinterStatus -eq 3)
} | Select-Object Name, PortName, DriverName, PrinterStatus, WorkOffline, Default
$printers | ConvertTo-Json -Depth 3 -Compress
POWERSHELL,
            <<<'POWERSHELL'
$printers = Get-Printer | Where-Object {
    $_.Name -and
    -not $_.WorkOffline -and
    ($_.PrinterStatus -eq 'Normal' -or $_.PrinterStatus -eq 'Idle' -or $_.PrinterStatus -eq 3)
} | Select-Object Name, PortName, DriverName, PrinterStatus, WorkOffline
$printers | ConvertTo-Json -Depth 3 -Compress
POWERSHELL,
        ];

        foreach ($scripts as $script) {
            $printers = $this->runPrinterScanScript($script);
            if ($printers !== []) {
                return $printers;
            }
        }

        return $this->scanWindowsPrintersFromRegistry();
    }

    /**
     * Initialize printer connector and printer instance
     */
    protected function initialize(?Tenant $tenant = null): void
    {
        $this->lastError = null;
        $settings = $this->resolvePrinterSettings($tenant);
        $connectionType = $settings['connection_type'];
        $address = $settings['address'];

        try {
            switch ($connectionType) {
                case 'network':
                    $this->connector = new NetworkPrintConnector($address, $settings['port']);
                    break;
                case 'windows':
                    $this->connector = new WindowsPrintConnector($address);
                    break;
                case 'file':
                    $this->connector = new FilePrintConnector($address);
                    break;
                default:
                    throw new Exception("Unsupported printer connection type: $connectionType");
            }

            $this->printer = new Printer($this->connector);
        } catch (Exception $e) {
            $this->lastError = $this->formatPrinterExceptionMessage($e);
            \Log::error('Printer initialization failed: '.$e->getMessage());
            $this->printer = null;
        }
    }

    protected function resolvePrinterSettings(?Tenant $tenant = null): array
    {
        $defaults = [
            'connection_type' => config('printer.connection_type', 'windows'),
            'address' => config('printer.address', 'POS-80'),
            'port' => (int) config('printer.port', 9100),
        ];

        if (! $tenant || ($tenant->printer_use_default ?? true)) {
            return $defaults;
        }

        return [
            'connection_type' => $tenant->printer_connection_type ?: $defaults['connection_type'],
            'address' => $tenant->printer_address ?: $defaults['address'],
            'port' => (int) ($tenant->printer_port ?: $defaults['port']),
        ];
    }

    protected function runPrinterScanScript(string $script): array
    {
        $process = new Process([
            'powershell.exe',
            '-NoProfile',
            '-NonInteractive',
            '-ExecutionPolicy',
            'Bypass',
            '-Command',
            $script,
        ]);

        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful()) {
            \Log::warning('Printer scan failed: '.$process->getErrorOutput());

            return [];
        }

        $output = trim($process->getOutput());
        if ($output === '' || $output === 'null') {
            return [];
        }

        $decoded = json_decode($output, true);
        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['Name'])) {
            $decoded = [$decoded];
        }

        return collect($decoded)
            ->filter(fn ($printer) => is_array($printer) && ! empty($printer['Name']))
            ->map(fn ($printer) => [
                'name' => $printer['Name'],
                'share_name' => $printer['ShareName'] ?? null,
                'is_shared' => ! empty($printer['ShareName']) || (bool) ($printer['Shared'] ?? false),
                'port_name' => $printer['PortName'] ?? null,
                'driver_name' => $printer['DriverName'] ?? null,
                'status' => $printer['PrinterStatus'] ?? null,
                'work_offline' => (bool) ($printer['WorkOffline'] ?? false),
                'is_default' => (bool) ($printer['Default'] ?? false),
            ])
            ->values()
            ->all();
    }

    protected function scanWindowsPrintersFromRegistry(): array
    {
        $regPath = getenv('SystemRoot')
            ? getenv('SystemRoot').'\System32\reg.exe'
            : 'C:\Windows\System32\reg.exe';

        $process = new Process([
            file_exists($regPath) ? $regPath : 'reg.exe',
            'query',
            'HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Print\Printers',
            '/s',
        ]);

        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful()) {
            \Log::warning('Printer registry scan failed: '.$process->getErrorOutput());

            return [];
        }

        $printers = [];
        $currentName = null;

        foreach (preg_split('/\R/', $process->getOutput()) as $line) {
            $line = rtrim($line);

            if (preg_match('/^HKEY_LOCAL_MACHINE\\\\SOFTWARE\\\\Microsoft\\\\Windows NT\\\\CurrentVersion\\\\Print\\\\Printers\\\\([^\\\\]+)$/', $line, $matches)) {
                $currentName = $matches[1];
                $printers[$currentName] = [
                    'name' => $currentName,
                    'share_name' => null,
                    'is_shared' => false,
                    'port_name' => null,
                    'driver_name' => null,
                    'status' => null,
                    'work_offline' => false,
                    'is_default' => false,
                ];

                continue;
            }

            if (! $currentName || ! isset($printers[$currentName])) {
                continue;
            }

            if (preg_match('/^\s+Share Name\s+REG_SZ\s*(.*)$/', $line, $matches)) {
                $shareName = trim($matches[1]);
                $printers[$currentName]['share_name'] = $shareName !== '' ? $shareName : null;
                $printers[$currentName]['is_shared'] = $shareName !== '';
            }

            if (preg_match('/^\s+Port\s+REG_SZ\s*(.*)$/', $line, $matches)) {
                $printers[$currentName]['port_name'] = trim($matches[1]) ?: null;
            }

            if (preg_match('/^\s+Printer Driver\s+REG_SZ\s*(.*)$/', $line, $matches)) {
                $printers[$currentName]['driver_name'] = trim($matches[1]) ?: null;
            }
        }

        return array_values($printers);
    }

    protected function resolveWindowsPrinterTarget(string $address): string
    {
        if (preg_match('/^(LPT\d|COM\d)$/', $address)) {
            return $address;
        }

        if (str_starts_with($address, 'smb://')) {
            $parts = parse_url($address);
            $path = ltrim($parts['path'] ?? '', '/');

            if (str_contains($path, '/')) {
                $path = explode('/', $path, 2)[1];
            }

            return '\\\\'.($parts['host'] ?? gethostname()).'\\'.$path;
        }

        return '\\\\'.(gethostname() ?: 'localhost').'\\'.$address;
    }

    protected function formatPrinterExceptionMessage(Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Failed to open stream') && preg_match('/copy\(([^)]+)\)/', $message, $matches)) {
            return 'Windows tidak dapat mengakses printer '.$matches[1].'. Pastikan printer di-share, permission share mengizinkan user yang menjalankan PHP untuk Print, dan alamat memakai Share Name.';
        }

        return $message;
    }

    /**
     * Format text into two columns
     */
    protected function formatTwoColumns(string $left, string $right, int $width): string
    {
        $leftWidth = $width - strlen($right);

        return str_pad($left, $leftWidth).$right;
    }
}
