<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: {{ $settings['font_family'] ?? 'Arial' }}, sans-serif;
            font-size: {{ $settings['font_size'] ?? '12' }}px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid {{ $settings['accent_color'] ?? '#3b82f6' }};
        }
        .company-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $settings['accent_color'] ?? '#3b82f6' }};
            margin: 10px 0;
        }
        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-box {
            width: 48%;
        }
        .info-box h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: {{ $settings['accent_color'] ?? '#3b82f6' }};
        }
        .info-box p {
            margin: 3px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table thead {
            background-color: {{ $settings['accent_color'] ?? '#3b82f6' }};
            color: white;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            width: 300px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .totals-row.total {
            font-size: 16px;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 12px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
            text-align: center;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 3px solid {{ $settings['accent_color'] ?? '#3b82f6' }};
        }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($settings['company_logo']))
        <img src="{{ public_path('storage/' . $settings['company_logo']) }}" class="company-logo" alt="Logo">
        @endif
        <div class="company-name">{{ $settings['company_name'] ?? 'PrintItMat' }}</div>
        <p>{{ $settings['company_address'] ?? '' }}</p>
        <p>{{ $settings['company_phone'] ?? '' }}</p>
    </div>
    
    <div class="document-title">QUOTATION: {{ $quotation->quotation_number }}</div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-box">
                <h3>Customer Details:</h3>
                <p><strong>{{ $quotation->customer_name }}</strong></p>
                @if($quotation->customer_company)
                <p>{{ $quotation->customer_company }}</p>
                @endif
                @if($quotation->customer_email)
                <p>{{ $quotation->customer_email }}</p>
                @endif
                @if($quotation->customer_phone)
                <p>{{ $quotation->customer_phone }}</p>
                @endif
                @if($quotation->customer_address)
                <p>{{ $quotation->customer_address }}</p>
                @endif
            </div>
            
            <div class="info-box text-right">
                <h3>Quotation Details:</h3>
                <p><strong>Date:</strong> {{ $quotation->created_at->format('M d, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($quotation->status) }}</p>
                @if($quotation->sent_at)
                <p><strong>Sent:</strong> {{ $quotation->sent_at->format('M d, Y') }}</p>
                @endif
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Size</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->size ?? '-' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>${{ number_format($quotation->subtotal, 2) }}</span>
        </div>
        @if($quotation->discount > 0)
        <div class="totals-row">
            <span>Discount:</span>
            <span>-${{ number_format($quotation->discount, 2) }}</span>
        </div>
        @endif
        @if($quotation->tax > 0)
        <div class="totals-row">
            <span>Tax:</span>
            <span>${{ number_format($quotation->tax, 2) }}</span>
        </div>
        @endif
        <div class="totals-row total">
            <span>Total:</span>
            <span>${{ number_format($quotation->total, 2) }}</span>
        </div>
    </div>
    
    @if($quotation->notes)
    <div class="notes">
        <strong>Notes:</strong><br>
        {{ $quotation->notes }}
    </div>
    @endif
    
    @if($settings['pdf_custom_notes'])
    <div class="notes">
        {!! nl2br(e($settings['pdf_custom_notes'])) !!}
    </div>
    @endif
    
    @if($settings['bank_details'])
    <div class="notes">
        <strong>Bank Details:</strong><br>
        {!! nl2br(e($settings['bank_details'])) !!}
    </div>
    @endif
    
    <div class="footer">
        {!! $settings['pdf_footer'] ?? 'Thank you for your business!' !!}
    </div>
</body>
</html>

