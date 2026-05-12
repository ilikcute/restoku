<!DOCTYPE html>
<html>
<head>
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        body { 
            font-family: 'Courier', monospace; 
            font-size: 11px; 
            color: #000; 
            width: 80mm; /* Simulating Thermal Width */
            margin: 0 auto;
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .tenant-name { font-size: 14px; margin-bottom: 2px; }
        
        .identity-section { margin-bottom: 10px; }
        .identity-table { width: 100%; }
        .identity-table td { padding: 1px 0; }
        
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        
        .item-table { width: 100%; margin-top: 5px; }
        .item-table th { text-align: left; border-bottom: 1px dashed #000; padding: 2px 0; }
        .category-name { font-weight: bold; margin-top: 8px; margin-bottom: 2px; text-decoration: underline; }
        
        .totals-section { margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .grand-total { font-size: 14px; margin-top: 5px; padding-top: 5px; border-top: 1px dashed #000; }
        
        .footer { margin-top: 20px; text-align: center; font-style: italic; font-size: 10px; }
        
        @page {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header text-center">
        @if($order->tenant->logo)
            <img src="{{ storage_path('app/public/' . $order->tenant->logo) }}" style="max-height: 50px; margin-bottom: 5px;">
        @endif
        <div class="tenant-name font-bold uppercase">{{ $order->tenant->name }}</div>
        @if($order->tenant->address)
            <div>{{ $order->tenant->address }}</div>
        @endif
        @if($order->tenant->phone)
            <div>Telp: {{ $order->tenant->phone }}</div>
        @endif
    </div>

    <div class="identity-section">
        <table class="identity-table">
            <tr>
                <td width="25%">Bill No</td>
                <td width="2%">:</td>
                <td>{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td>Name</td>
                <td>:</td>
                <td>{{ $order->customer_name ?? '-' }}</td>
            </tr>
        </table>
        <table class="identity-table" style="margin-top: 2px;">
            <tr>
                <td width="25%">Date</td>
                <td width="2%">:</td>
                <td width="25%">{{ $order->created_at->format('d/m/Y') }}</td>
                <td width="15%">Shift</td>
                <td width="2%">:</td>
                <td>{{ $order->shift->name ?? ($order->shift_id ? 'Shift #'.$order->shift_id : 'NIGHT') }}</td>
            </tr>
            <tr>
                <td>Table</td>
                <td>:</td>
                <td>{{ $order->table_number ?? '-' }}</td>
                <td>Cashier</td>
                <td>:</td>
                <td>{{ $order->user->name }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>
    <table class="item-table" style="font-weight: bold;">
        <thead>
            <tr>
                <th width="10%">QTY</th>
                <th width="60%">DESCRIPTION</th>
                <th width="30%" class="text-right">TOTAL</th>
            </tr>
        </thead>
    </table>

    @php
        $itemsByCategory = $order->items->groupBy(function($item) {
            return strtoupper($item->product->category->name ?? 'OTH');
        });
    @endphp

    @foreach($itemsByCategory as $categoryName => $items)
        <div class="category-name">{{ $categoryName }}</div>
        <table class="item-table" style="margin-top: 0;">
            @php $catSubtotal = 0; @endphp
            @foreach($items as $item)
                @php 
                    $finalQty = $item->quantity - $item->return_quantity;
                    $isReturned = $item->return_quantity > 0;
                @endphp
                <tr>
                    <td width="10%">
                        @if($isReturned)
                            <span style="text-decoration: line-through; color: #666;">{{ $item->quantity }}</span><br>
                            <span style="color: #e11d48;">R:{{ $item->return_quantity }}</span><br>
                            <span style="font-weight: black;">{{ $finalQty }}</span>
                        @else
                            {{ $item->quantity }}
                        @endif
                    </td>
                    <td width="60%">
                        {{ $item->product_name }}
                        @if($item->discount_amount > 0)
                            <br>
                            <span style="font-size: 9px; font-weight: normal; font-style: italic;">(Potongan: -{{ number_format($item->discount_amount, 0, ',', '.') }})</span>
                        @endif
                    </td>
                    <td width="30%" class="text-right">
                        @if($isReturned)
                            <span style="text-decoration: line-through; color: #666;">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span><br>
                            <span style="color: #e11d48;">-{{ number_format($item->return_amount, 0, ',', '.') }}</span><br>
                            <span style="font-weight: black;">{{ number_format(($item->price * $item->quantity) - $item->return_amount, 0, ',', '.') }}</span>
                        @else
                            {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
                @php $catSubtotal += ($item->subtotal - $item->return_amount); @endphp
            @endforeach
            <tr class="font-bold">
                <td colspan="2" class="text-right" style="padding-top: 4px;">TOTAL {{ $categoryName }}</td>
                <td class="text-right" style="padding-top: 4px;">{{ number_format($catSubtotal, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="divider"></div>
    @endforeach

    <div class="totals-section">
        <div class="total-row">
            <span>SUB TOTAL (GROSS)</span>
            <span>{{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($order->discount_amount > 0)
        <div class="total-row">
            <span>TOTAL DISKON</span>
            <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row">
            <span>SERVICE</span>
            <span>{{ number_format($order->service_charge, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>TAX</span>
            <span>{{ number_format($order->tax_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>ROUNDING</span>
            <span>{{ number_format($order->rounding, 0, ',', '.') }}</span>
        </div>

        @if($order->total_return > 0)
        <div class="total-row" style="color: #e11d48; font-weight: bold;">
            <span>TOTAL RETUR</span>
            <span>-{{ number_format($order->total_return, 0, ',', '.') }}</span>
        </div>
        @endif
        
        <div class="divider" style="margin-top: 10px;"></div>
        <div class="total-row grand-total font-bold uppercase">
            <span style="font-size: 14px;">GRAND TOTAL</span>
            <span style="font-size: 14px;">Rp {{ number_format($order->total_amount - ($order->total_return ?? 0), 0, ',', '.') }}</span>
        </div>
        <div class="divider"></div>
        
        <div class="total-row">
            <span>Total Paid</span>
            <span>{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Change</span>
            <span>{{ number_format($order->change_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        @if($order->tenant->footer_text)
            {!! nl2br(e($order->tenant->footer_text)) !!}
        @else
            Terima Kasih!<br>Selamat Menikmati Hidangan Kami
        @endif
    </div>
</body>
</html>
