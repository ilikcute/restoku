<!DOCTYPE html>
<html>

<head>
    <title>Purchase Receipt - {{ $purchase->purchase_number }}</title>
    <style>
        body {
            font-family: 'Courier', monospace;
            font-size: 11px;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .tenant-name {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .identity-section {
            margin-bottom: 10px;
        }

        .identity-table {
            width: 100%;
        }

        .identity-table td {
            padding: 1px 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .item-table {
            width: 100%;
            margin-top: 5px;
        }

        .item-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 2px 0;
        }

        .totals-section {
            margin-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .grand-total {
            font-size: 14px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-style: italic;
            font-size: 10px;
        }

        @page {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="header text-center">
        <div class="tenant-name font-bold uppercase">{{ $purchase->tenant->name }}</div>
        <div>FAKTUR PEMBELIAN STOK</div>
    </div>

    <div class="identity-section">
        <table class="identity-table">
            <tr>
                <td width="30%">No. Bukti</td>
                <td width="2%">:</td>
                <td>{{ $purchase->purchase_number }}</td>
            </tr>
            <tr>
                <td>Pemasok</td>
                <td>:</td>
                <td>{{ $purchase->supplier->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Karyawan</td>
                <td>:</td>
                <td>{{ $purchase->user->name }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>
    <table class="item-table">
        <thead>
            <tr>
                <th width="10%">QTY</th>
                <th width="50%">NAMA PRODUK</th>
                <th width="40%" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                @php
                    $finalQty = $item->quantity - $item->return_quantity;
                    $isReturned = $item->return_quantity > 0;
                @endphp
                <tr>
                    <td width="10%">
                        @if ($isReturned)
                            <span style="text-decoration: line-through; color: #666;">{{ $item->quantity }}</span><br>
                            <span style="color: #e11d48;">R:{{ $item->return_quantity }}</span><br>
                            <span style="font-weight: black;">{{ $finalQty }}</span>
                        @else
                            {{ $item->quantity }}
                        @endif
                    </td>
                    <td width="50%">{{ $item->product_name }}</td>
                    <td width="40%" class="text-right">
                        @if ($isReturned)
                            <span
                                style="text-decoration: line-through; color: #666;">{{ number_format($item->subtotal, 0, ',', '.') }}</span><br>
                            <span
                                style="color: #e11d48;">-{{ number_format($item->return_amount, 0, ',', '.') }}</span><br>
                            <span
                                style="font-weight: black;">{{ number_format($item->subtotal - $item->return_amount, 0, ',', '.') }}</span>
                        @else
                            {{ number_format($item->subtotal, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals-section">
        <div class="total-row">
            <span>SUB TOTAL</span>
            <span>{{ number_format($purchase->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>PAJAK</span>
            <span>{{ number_format($purchase->tax_amount, 0, ',', '.') }}</span>
        </div>
        @if ($purchase->total_return > 0)
            <div class="total-row" style="color: #e11d48; font-weight: bold;">
                <span>TOTAL RETUR</span>
                <span>-{{ number_format($purchase->total_return, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="divider" style="margin-top: 10px;"></div>
        <div class="total-row grand-total font-bold uppercase">
            <span style="font-size: 14px;">GRAND TOTAL</span>
            <span style="font-size: 14px;">Rp
                {{ number_format($purchase->total_amount - ($purchase->total_return ?? 0), 0, ',', '.') }}</span>
        </div>
        <div class="divider"></div>

        @if ($purchase->notes)
            <div style="margin-top: 5px; font-size: 9px;">
                <strong>Catatan:</strong><br>
                {{ $purchase->notes }}
            </div>
        @endif
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
