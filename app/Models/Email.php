<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'assigned_to',
        'message_id',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body_html',
        'body_text',
        'is_read',
        'is_sent',
        'has_attachments',
        'email_date',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_sent' => 'boolean',
        'has_attachments' => 'boolean',
        'email_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    public function markAsUnread()
    {
        if ($this->is_read) {
            $this->update(['is_read' => false]);
        }
    }

    public static function autoLinkToCustomer($email)
    {
        // Try to find customer by email
        $customer = Customer::where('email', $email->from_email)->first();

        if (!$customer) {
            // Try to find by phone in message body
            if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $email->body_text, $matches)) {
                $phone = preg_replace('/[^0-9]/', '', $matches[0]);
                $customer = Customer::where('phone', 'LIKE', "%{$phone}%")->first();
            }
        }

        if ($customer) {
            $email->customer_id = $customer->id;
            $email->save();
        }

        return $customer;
    }
}

