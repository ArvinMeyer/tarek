<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $po->po_number }}</title>
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
    
    <div class="document-title">PURCHASE ORDER: {{ $po->po_number }}</div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-box">
                <h3>Supplier:</h3>
                <p><strong>{{ $po->supplier_name }}</strong></p>
                @if($po->supplier_email)
                <p>{{ $po->supplier_email }}</p>
                @endif
                @if($po->supplier_phone)
                <p>{{ $po->supplier_phone }}</p>
                @endif
                @if($po->supplier_address)
                <p>{{ $po->supplier_address }}</p>
                @endif
            </div>
            
            <div class="info-box text-right">
                <h3>PO Details:</h3>
                <p><strong>Date:</strong> {{ $po->created_at->format('M d, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($po->status) }}</p>
                @if($po->sent_at)
                <p><strong>Sent:</strong> {{ $po->sent_at->format('M d, Y') }}</p>
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
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->size ?? '-' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($po->notes)
    <div class="notes">
        <strong>Notes:</strong><br>
        {{ $po->notes }}
    </div>
    @endif
    
    @if($settings['pdf_custom_notes'])
    <div class="notes">
        {!! nl2br(e($settings['pdf_custom_notes'])) !!}
    </div>
    @endif
    
    <div class="footer">
        {!! $settings['pdf_footer'] ?? 'Thank you for your business!' !!}
    </div>
</body>
</html>

