<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // quotation_us, quotation_uk, quotation_ca, invoice_us, etc.
            $table->integer('counter')->default(0);
            $table->timestamps();
            
            $table->unique('type');
        });

        // Initialize counters
        DB::table('counters')->insert([
            ['type' => 'quotation_us', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'quotation_uk', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'quotation_ca', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'invoice_us', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'invoice_uk', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'invoice_ca', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'po', 'counter' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};

