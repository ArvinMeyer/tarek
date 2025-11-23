<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share settings with all views
        View::composer('*', function ($view) {
            if (schema()->hasTable('settings')) {
                $view->with('appSettings', [
                    'company_name' => Setting::get('company_name', 'PrintItMat'),
                    'company_logo' => Setting::get('company_logo', ''),
                ]);
            }
        });

        // Custom Blade directives for permissions
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('manager', function () {
            return auth()->check() && auth()->user()->isManager();
        });

        Blade::if('staff', function () {
            return auth()->check() && auth()->user()->isStaff();
        });

        Blade::if('viewer', function () {
            return auth()->check() && auth()->user()->isViewer();
        });

        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
    }
}

