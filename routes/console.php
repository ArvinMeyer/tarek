<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Fetch emails every 5 minutes (can be disabled for Hostinger shared hosting)
// Schedule::command('emails:fetch')->everyFiveMinutes();

