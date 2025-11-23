<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'quotation_id',
        'created_by',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'customer_country',
        'customer_street',
        'customer_city',
        'customer_address',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'payment_status',
        'status',
        'due_date',
        'notes',
        'sent_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total');
        $this->total = $this->subtotal - $this->discount + $this->tax;
        $this->save();
    }

    public function updatePaymentStatus()
    {
        $this->paid_amount = $this->payments->sum('amount');

        if ($this->paid_amount >= $this->total) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->due_date && Carbon::parse($this->due_date)->isPast()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'unpaid';
        }

        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->payment_status === 'overdue' || 
               ($this->due_date && Carbon::parse($this->due_date)->isPast() && $this->paid_amount < $this->total);
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    public static function generateNumber(string $country): string
    {
        $prefix = match(strtoupper($country)) {
            'US' => 'USIN',
            'UK' => 'UKIN',
            'CA' => 'CAIN',
            default => 'USIN',
        };

        $counterType = 'invoice_' . strtolower($country);
        
        $counter = \DB::table('counters')
            ->where('type', $counterType)
            ->lockForUpdate()
            ->first();

        if (!$counter) {
            \DB::table('counters')->insert([
                'type' => $counterType,
                'counter' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $nextNumber = 1;
        } else {
            $nextNumber = $counter->counter + 1;
            \DB::table('counters')
                ->where('type', $counterType)
                ->update([
                    'counter' => $nextNumber,
                    'updated_at' => now(),
                ]);
        }

        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}

