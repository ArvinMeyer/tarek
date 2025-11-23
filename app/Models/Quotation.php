<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'customer_id',
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
        'status',
        'notes',
        'sent_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total');
        $this->total = $this->subtotal - $this->discount + $this->tax;
        $this->save();
    }

    public static function generateNumber(string $country): string
    {
        $prefix = match(strtoupper($country)) {
            'US' => 'USQ',
            'UK' => 'UKQ',
            'CA' => 'CAQ',
            default => 'USQ',
        };

        $counterType = 'quotation_' . strtolower($country);
        
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

