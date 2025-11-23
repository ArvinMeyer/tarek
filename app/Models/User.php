<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'theme',
        'force_password_change',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'force_password_change' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'admin' => ['*'], // All permissions
            'manager' => [
                'quotations.create', 'quotations.edit', 'quotations.view',
                'invoices.create', 'invoices.edit', 'invoices.view',
                'customers.create', 'customers.edit', 'customers.view',
                'po.create', 'po.edit', 'po.view',
                'emails.view', 'emails.send',
            ],
            'staff' => [
                'quotations.create', 'quotations.edit', 'quotations.view',
                'invoices.create', 'invoices.edit', 'invoices.view',
                'customers.view',
                'emails.view', 'emails.send',
            ],
            'viewer' => [
                'quotations.view',
                'invoices.view',
                'customers.view',
                'emails.view',
            ],
        ];

        $userPermissions = $permissions[$this->role] ?? [];
        
        return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
    }

    // Relationships
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function assignedEmails()
    {
        return $this->hasMany(Email::class, 'assigned_to');
    }
}

