<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'country',
        'street',
        'city',
        'full_address',
        'tags',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function files()
    {
        return $this->hasMany(CustomerFile::class);
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->street,
            $this->city,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }

    public static function findOrCreateByEmail($email, $data = [])
    {
        if ($customer = self::where('email', $email)->first()) {
            return $customer;
        }

        return self::create(array_merge(['email' => $email], $data));
    }
}

