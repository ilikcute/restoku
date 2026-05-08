<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .tenant-name { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .report-title { font-size: 14px; margin-top: 5px; color: #666; }
        .period { font-size: 11px; margin-top: 5px; color: #888; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; background-color: #f2f2f2; padding: 8px; border-bottom: 1px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        
        .section-title { font-weight: bold; background-color: #f9f9f9; font-size: 13px; color: #2c3e50; }
        .amount { text-align: right; font-family: 'Courier', monospace; }
        .total-row { font-weight: bold; background-color: #ebf5fb; }
        .net-profit-row { font-weight: bold; background-color: #e8f8f5; font-size: 14px; }
        .negative { color: #c0392b; }
        
        .footer { margin-top: 50px; font-size: 10px; text-align: right; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ storage_path('app/public/' . $tenant->logo) }}" style="height: 60px; margin-bottom: 10px; object-fit: contain;">
        @endif
        <div class="tenant-name">{{ $tenant->name }}</div>
        @if($tenant->address)
            <div style="font-size: 10px; color: #666; margin-top: 2px;">{{ $tenant->address }}</div>
        @endif
        @if($tenant->phone)
            <div style="font-size: 10px; color: #666;">Telp: {{ $tenant->phone }}</div>
        @endif
        <div class="report-title" style="margin-top: 10px; border-top: 1px dashed #eee; pt-2;">LAPORAN REKAPITULASI KEUANGAN</div>
        <div class="period">Periode: {{ $start_date }} s/d {{ $end_date }}</div>
    </div>

    <table>
        <thead>
            <tr class="section-title">
                <th colspan="2">PENDAPATAN & PENJUALAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Penjualan Kotor (Gross)</td>
                <td class="amount">Rp {{ number_format($sales->gross_sales, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>- Total Pajak (PB1/PPN)</td>
                <td class="amount negative">(Rp {{ number_format($sales->total_tax, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>- Total Service Charge</td>
                <td class="amount negative">(Rp {{ number_format($sales->total_service, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>- Total Diskon</td>
                <td class="amount negative">(Rp {{ number_format($sales->total_discount, 0, ',', '.') }})</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL PENJUALAN BERSIH (NET)</td>
                <td class="amount">Rp {{ number_format($sales->net_sales, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr class="section-title">
                <th colspan="2">BEBAN & PROFITABILITAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total HPP (Modal Produk)</td>
                <td class="amount negative">(Rp {{ number_format($cogs, 0, ',', '.') }})</td>
            </tr>
            <tr class="total-row">
                <td>GROSS PROFIT</td>
                <td class="amount" style="color: #27ae60;">Rp {{ number_format($gross_profit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Beban Operasional (Expense)</td>
                <td class="amount negative">(Rp {{ number_format($expenses, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>Pendapatan Lain-lain</td>
                <td class="amount">Rp {{ number_format($other_income, 0, ',', '.') }}</td>
            </tr>
            <tr class="net-profit-row">
                <td>ESTIMASI LABA BERSIH</td>
                <td class="amount" style="color: #2980b9;">Rp {{ number_format($net_profit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
