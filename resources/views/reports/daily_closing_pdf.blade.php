<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tutup Harian - {{ $closing->closing_date }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 297mm; /* Thermal paper size */
        }
        body {
            font-family: 'Courier', monospace;
            font-size: 11px;
            line-height: 1.4;
            padding: 10px;
            margin: 0;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 50px;
            margin-bottom: 5px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 10px;
            margin-bottom: 2px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .total-row {
            font-weight: bold;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 9px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = null;
            if ($tenant->logo_url) {
                $rawPath = str_replace('/storage/', '', $tenant->logo_url);
                $fullPath = storage_path('app/public/' . $rawPath);
                if (file_exists($fullPath)) {
                    $logoPath = $fullPath;
                }
            }
        @endphp
        @if($logoPath)
            <img src="{{ $logoPath }}" class="logo">
        @endif
        <div class="title">{{ $tenant->name }}</div>
        <div class="subtitle">{{ $tenant->address }}</div>
    </div>

    <div class="divider"></div>
    <div class="text-center" style="font-weight: bold; margin-bottom: 5px;">LAPORAN TUTUP HARIAN (EOD)</div>
    <div class="divider"></div>

    <table>
        <tr>
            <td>Tanggal</td>
            <td class="text-right">: {{ \Carbon\Carbon::parse($closing->closing_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Petugas</td>
            <td class="text-right">: {{ $closing->user->name }}</td>
        </tr>
        <tr>
            <td>Waktu Cetak</td>
            <td class="text-right">: {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="section-title">RINGKASAN OPERASIONAL</div>
    <table>
        <tr>
            <td>Total Penjualan</td>
            <td class="text-right">{{ number_format($closing->total_revenue, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Transaksi</td>
            <td class="text-right">{{ $closing->total_transactions }}</td>
        </tr>
        <tr>
            <td>Total Pajak</td>
            <td class="text-right">{{ number_format($closing->total_tax, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="section-title">REKAPITULASI KEUANGAN</div>
    <table>
        <tr>
            <td>Penjualan Bruto (+)</td>
            <td class="text-right">{{ number_format($closing->total_revenue, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pendapatan Lain (+)</td>
            <td class="text-right">{{ number_format($closing->total_income, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pengeluaran (-)</td>
            <td class="text-right">-{{ number_format($closing->total_expense, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row" style="border-top: 1px solid #000; padding-top: 5px;">
            <td>PENDAPATAN BERSIH</td>
            <td class="text-right">{{ number_format($closing->net_revenue, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($closing->notes)
    <div class="divider"></div>
    <div class="section-title">CATATAN HARIAN</div>
    <div style="font-style: italic;">"{{ $closing->notes }}"</div>
    @endif

    <div class="divider"></div>
    <div class="footer">
        Dicetak otomatis oleh Restoku POS<br>
        Terima kasih atas kerja keras hari ini!
    </div>
</body>
</html>
