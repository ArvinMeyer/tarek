<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuditLogController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth', 'force.password.change'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Change Password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Theme Update
    Route::post('/update-theme', [AuthController::class, 'updateTheme'])->name('update-theme');
    
    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    
    // Customers (CRM)
    Route::middleware('permission:customers.view')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::post('customers/{customer}/upload', [CustomerController::class, 'uploadFile'])->name('customers.upload');
    });
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])
        ->name('customers.destroy')
        ->middleware('role:admin');
    Route::delete('customer-files/{file}', [CustomerController::class, 'deleteFile'])
        ->name('customer-files.destroy')
        ->middleware('role:admin');
    
    // Quotations
    Route::middleware('permission:quotations.view')->group(function () {
        Route::resource('quotations', QuotationController::class)->except(['destroy']);
        Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
        Route::post('quotations/{quotation}/email', [QuotationController::class, 'sendEmail'])->name('quotations.email');
        Route::post('quotations/autosave', [QuotationController::class, 'autosave'])->name('quotations.autosave');
    });
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])
        ->name('quotations.destroy')
        ->middleware('role:admin');
    
    // Invoices
    Route::middleware('permission:invoices.view')->group(function () {
        Route::resource('invoices', InvoiceController::class)->except(['destroy']);
        Route::post('invoices/{invoice}/hold', [InvoiceController::class, 'hold'])->name('invoices.hold')->middleware('role:admin');
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.payment');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/email', [InvoiceController::class, 'sendEmail'])->name('invoices.email');
    });
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
        ->name('invoices.destroy')
        ->middleware('role:admin');
    
    // Purchase Orders
    Route::middleware('permission:po.view')->group(function () {
        Route::resource('purchase-orders', PurchaseOrderController::class)->except(['destroy']);
        Route::post('purchase-orders/{purchaseOrder}/received', [PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.received');
        Route::get('purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchase-orders.pdf');
        Route::post('purchase-orders/{purchaseOrder}/email', [PurchaseOrderController::class, 'sendEmail'])->name('purchase-orders.email');
        Route::post('invoices/{invoice}/create-po', [PurchaseOrderController::class, 'createFromInvoice'])->name('invoices.create-po');
    });
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])
        ->name('purchase-orders.destroy')
        ->middleware('role:admin');
    
    // Emails
    Route::middleware('permission:emails.view')->group(function () {
        Route::get('emails', [EmailController::class, 'index'])->name('emails.index');
        Route::get('emails/{email}', [EmailController::class, 'show'])->name('emails.show');
        Route::post('emails/fetch', [EmailController::class, 'fetch'])->name('emails.fetch');
        Route::post('emails/{email}/read', [EmailController::class, 'markAsRead'])->name('emails.read');
        Route::post('emails/{email}/unread', [EmailController::class, 'markAsUnread'])->name('emails.unread');
        Route::post('emails/{email}/assign', [EmailController::class, 'assign'])->name('emails.assign');
        Route::post('emails/{email}/link', [EmailController::class, 'linkToCustomer'])->name('emails.link');
        Route::get('emails/compose/new', [EmailController::class, 'compose'])->name('emails.compose');
        Route::post('emails/send', [EmailController::class, 'send'])->name('emails.send')->middleware('permission:emails.send');
        Route::post('emails/{email}/reply', [EmailController::class, 'reply'])->name('emails.reply')->middleware('permission:emails.send');
        Route::post('emails/{email}/forward', [EmailController::class, 'forward'])->name('emails.forward')->middleware('permission:emails.send');
    });
    
    // Settings (Admin & Manager)
    Route::middleware('role:admin,manager')->prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        
        // Company Settings
        Route::get('/company', [SettingsController::class, 'company'])->name('settings.company');
        Route::post('/company', [SettingsController::class, 'updateCompany']);
        
        // Email Settings
        Route::get('/email', [SettingsController::class, 'email'])->name('settings.email');
        Route::post('/email', [SettingsController::class, 'updateEmail']);
        
        // Email Signature
        Route::get('/signature', [SettingsController::class, 'signature'])->name('settings.signature');
        Route::post('/signature', [SettingsController::class, 'updateSignature']);
        
        // PDF Settings
        Route::get('/pdf', [SettingsController::class, 'pdf'])->name('settings.pdf');
        Route::post('/pdf', [SettingsController::class, 'updatePdf']);
    });
    
    // Users Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]);
    });
    
    // Backup & Restore (Admin only)
    Route::middleware('role:admin')->prefix('backups')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
    
    // Audit Logs (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

// Fallback route
Route::fallback(function () {
    abort(404);
});

