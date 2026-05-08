<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Shift - {{ $shift->id }}</title>
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
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table td {
            vertical-align: top;
        }
        .label {
            width: 80px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
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
        <div class="subtitle">Telp: {{ $tenant->phone }}</div>
    </div>

    <div class="divider"></div>
    <div class="text-center" style="font-weight: bold; margin-bottom: 5px;">LAPORAN PENUTUPAN SHIFT</div>
    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td class="label">No. Shift</td>
            <td>: #{{ $shift->id }}</td>
        </tr>
        <tr>
            <td class="label">Kasir</td>
            <td>: {{ $shift->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Buka</td>
            <td>: {{ $shift->start_time->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Tutup</td>
            <td>: {{ $shift->end_time ? $shift->end_time->format('d/m/Y H:i') : 'AKTIF' }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="section-title">RINGKASAN PENJUALAN</div>
    <table>
        <tr>
            <td>Penjualan Tunai</td>
            <td class="text-right">{{ number_format($shift->cash_sales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Penjualan Non-Tunai</td>
            <td class="text-right">{{ number_format($shift->non_cash_sales, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL PENJUALAN</td>
            <td class="text-right">{{ number_format($shift->total_sales, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="section-title">REKONSILIASI KAS (LACI)</div>
    <table>
        <tr>
            <td>Modal Awal</td>
            <td class="text-right">{{ number_format($shift->starting_cash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Penjualan Tunai (+)</td>
            <td class="text-right">{{ number_format($shift->cash_sales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pendapatan Lain (+)</td>
            <td class="text-right">{{ number_format($shift->total_income, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pengeluaran/Biaya (-)</td>
            <td class="text-right">-{{ number_format($shift->total_expense, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row" style="border-top: 1px solid #000; padding-top: 5px;">
            <td>KAS SEHARUSNYA</td>
            <td class="text-right">{{ number_format($shift->total_expected, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>KAS FISIK (AKTUAL)</td>
            <td class="text-right">{{ number_format($shift->ending_cash, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>SELISIH KAS</td>
            <td class="text-right">{{ $shift->difference > 0 ? '+' : '' }}{{ number_format($shift->difference, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($shift->notes)
    <div class="divider"></div>
    <div class="section-title">CATATAN</div>
    <div style="font-style: italic;">"{{ $shift->notes }}"</div>
    @endif

    <div class="divider"></div>
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}<br>
        Restoku POS - Solusi Bisnis Anda
    </div>
</body>
</html>
