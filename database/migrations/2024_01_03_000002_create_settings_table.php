<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, text, json, boolean
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            // Company Settings
            ['key' => 'company_name', 'value' => 'PrintItMat', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_address', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_phone', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_logo', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'bank_details', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            
            // Email Settings
            ['key' => 'smtp_host', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_username', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_password', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'imap_host', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'imap_port', 'value' => '993', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'imap_username', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'imap_password', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'email_signature', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            
            // PDF Settings
            ['key' => 'pdf_header', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_footer', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_font_size', 'value' => '12', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_font_family', 'value' => 'Arial', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_accent_color', 'value' => '#3b82f6', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_margin_top', 'value' => '15', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_margin_bottom', 'value' => '15', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_margin_left', 'value' => '15', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_margin_right', 'value' => '15', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pdf_custom_notes', 'value' => '', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

