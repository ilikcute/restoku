<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pajak (DPKAD)</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .tenant-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .report-title { font-size: 12px; margin-top: 5px; font-weight: bold; }
        .period { font-size: 10px; margin-top: 2px; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th { background-color: #f2f2f2; padding: 6px 4px; border: 1px solid #ccc; text-align: center; font-weight: bold; }
        td { padding: 6px 4px; border: 1px solid #eee; text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        
        .footer-summary { margin-top: 20px; width: 40%; float: right; }
        .footer-summary table td { border: none; padding: 4px 0; }
        .footer-summary .label { text-align: left; font-weight: bold; }
        .footer-summary .value { text-align: right; font-weight: bold; font-size: 12px; }
        .grand-total { border-top: 1px solid #333 !important; pt-2; color: #27ae60; }
        
        .print-info { margin-top: 50px; font-size: 8px; color: #999; clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <div class="tenant-name">{{ $tenant->name }}</div>
        <div class="report-title">LAPORAN REKAPITULASI PENJUALAN TERLAPOR (TAX)</div>
        <div class="period">Periode: {{ date('d/m/Y', strtotime($start_date)) }} s/d {{ date('d/m/Y', strtotime($end_date)) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Tanggal</th>
                @foreach($allCategories as $cat)
                    <th>{{ $cat }}</th>
                @endforeach
                <th>Subtotal</th>
                <th>Service</th>
                <th>Pajak (PB1)</th>
                <th>Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalSubtotal = 0; 
                $totalService = 0; 
                $totalTax = 0; 
                $totalGrand = 0;
                $catTotals = [];
                foreach($allCategories as $cat) $catTotals[$cat] = 0;
            @endphp
            @foreach($reportData as $day)
                <tr>
                    <td class="text-left">
                        <strong>{{ date('d/m/Y', strtotime($day['date'])) }}</strong><br>
                        <small>{{ $day['day'] }}</small>
                    </td>
                    @foreach($allCategories as $cat)
                        @php $val = $day['categories'][$cat] ?? 0; $catTotals[$cat] += $val; @endphp
                        <td>{{ number_format($val, 0, ',', '.') }}</td>
                    @endforeach
                    <td>{{ number_format($day['subtotal'], 0, ',', '.') }}</td>
                    <td>{{ number_format($day['service'], 0, ',', '.') }}</td>
                    <td style="color: #c0392b; font-weight: bold;">{{ number_format($day['tax'], 0, ',', '.') }}</td>
                    <td style="background-color: #f9f9f9; font-weight: bold;">{{ number_format($day['grand_total'], 0, ',', '.') }}</td>
                </tr>
                @php 
                    $totalSubtotal += $day['subtotal'];
                    $totalService += $day['service'];
                    $totalTax += $day['tax'];
                    $totalGrand += $day['grand_total'];
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td class="text-center">TOTAL</td>
                @foreach($allCategories as $cat)
                    <td>{{ number_format($catTotals[$cat], 0, ',', '.') }}</td>
                @endforeach
                <td>{{ number_format($totalSubtotal, 0, ',', '.') }}</td>
                <td>{{ number_format($totalService, 0, ',', '.') }}</td>
                <td>{{ number_format($totalTax, 0, ',', '.') }}</td>
                <td>{{ number_format($totalGrand, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-summary">
        <table>
            <tr>
                <td class="label">Total DPP (Net)</td>
                <td class="value">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Pajak (PB1)</td>
                <td class="value" style="color: #c0392b;">Rp {{ number_format($totalTax, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td class="label">TOTAL TERLAPOR</td>
                <td class="value">Rp {{ number_format($totalGrand, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="print-info">
        Laporan ini hanya menampilkan data yang sudah disinkronkan ke sistem DPKAD.<br>
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
