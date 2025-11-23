<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('country')->nullable();
            $table->text('street')->nullable();
            $table->string('city')->nullable();
            $table->text('full_address')->nullable();
            $table->string('tags')->nullable(); // VIP, Urgent, Blocked, etc.
            $table->longText('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['email', 'phone']);
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

