<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'username' => 'admin',
            'email' => 'admin@printitmat.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'theme' => 'light',
            'force_password_change' => true,
            'is_active' => true,
        ]);

        // Create sample users for different roles
        User::create([
            'username' => 'manager',
            'email' => 'manager@printitmat.com',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'theme' => 'light',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'staff',
            'email' => 'staff@printitmat.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'theme' => 'light',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'viewer',
            'email' => 'viewer@printitmat.com',
            'password' => Hash::make('viewer123'),
            'role' => 'viewer',
            'theme' => 'light',
            'is_active' => true,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('=== DEFAULT LOGIN CREDENTIALS ===');
        $this->command->info('Admin:');
        $this->command->info('  Username: admin');
        $this->command->info('  Password: admin123');
        $this->command->info('');
        $this->command->info('Manager:');
        $this->command->info('  Username: manager');
        $this->command->info('  Password: manager123');
        $this->command->info('');
        $this->command->info('Staff:');
        $this->command->info('  Username: staff');
        $this->command->info('  Password: staff123');
        $this->command->info('');
        $this->command->info('Viewer:');
        $this->command->info('  Username: viewer');
        $this->command->info('  Password: viewer123');
        $this->command->info('=================================');
    }
}

