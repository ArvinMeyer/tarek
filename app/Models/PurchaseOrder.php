<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'invoice_id',
        'created_by',
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'supplier_address',
        'status',
        'notes',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    public static function generateNumber(): string
    {
        $counter = \DB::table('counters')
            ->where('type', 'po')
            ->lockForUpdate()
            ->first();

        if (!$counter) {
            \DB::table('counters')->insert([
                'type' => 'po',
                'counter' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $nextNumber = 1;
        } else {
            $nextNumber = $counter->counter + 1;
            \DB::table('counters')
                ->where('type', 'po')
                ->update([
                    'counter' => $nextNumber,
                    'updated_at' => now(),
                ]);
        }

        return 'PO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function createFromInvoice(Invoice $invoice, array $data = []): self
    {
        $po = self::create(array_merge([
            'po_number' => self::generateNumber(),
            'invoice_id' => $invoice->id,
            'created_by' => auth()->id(),
            'supplier_name' => $data['supplier_name'] ?? '',
            'supplier_email' => $data['supplier_email'] ?? '',
            'supplier_phone' => $data['supplier_phone'] ?? '',
            'supplier_address' => $data['supplier_address'] ?? '',
            'status' => 'pending',
        ], $data));

        // Copy items from invoice (without prices)
        foreach ($invoice->items as $invoiceItem) {
            $po->items()->create([
                'product_name' => $invoiceItem->product_name,
                'size' => $invoiceItem->size,
                'quantity' => $invoiceItem->quantity,
                'sort_order' => $invoiceItem->sort_order,
            ]);
        }

        return $po;
    }
}

