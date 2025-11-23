<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function company()
    {
        return view('settings.company');
    }

    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string',
            'bank_details' => 'nullable|string',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        Setting::set('company_name', $request->company_name);
        Setting::set('company_address', $request->company_address);
        Setting::set('company_phone', $request->company_phone);
        Setting::set('bank_details', $request->bank_details);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path);
        }

        AuditLog::log('updated', 'settings', 'company', null, $request->all());

        return back()->with('success', 'Company settings updated successfully!');
    }

    public function email()
    {
        return view('settings.email');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'required|in:tls,ssl',
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_username' => 'required|string',
            'imap_password' => 'nullable|string',
        ]);

        Setting::set('smtp_host', $request->smtp_host);
        Setting::set('smtp_port', $request->smtp_port);
        Setting::set('smtp_username', $request->smtp_username);
        
        if ($request->filled('smtp_password')) {
            Setting::set('smtp_password', $request->smtp_password);
        }
        
        Setting::set('smtp_encryption', $request->smtp_encryption);
        Setting::set('imap_host', $request->imap_host);
        Setting::set('imap_port', $request->imap_port);
        Setting::set('imap_username', $request->imap_username);
        
        if ($request->filled('imap_password')) {
            Setting::set('imap_password', $request->imap_password);
        }

        AuditLog::log('updated', 'settings', 'email', null, ['smtp_host' => $request->smtp_host]);

        return back()->with('success', 'Email settings updated successfully!');
    }

    public function signature()
    {
        return view('settings.signature');
    }

    public function updateSignature(Request $request)
    {
        $request->validate([
            'email_signature' => 'nullable|string',
        ]);

        Setting::set('email_signature', $request->email_signature, 'text');

        AuditLog::log('updated', 'settings', 'signature', null, null);

        return back()->with('success', 'Email signature updated successfully!');
    }

    public function pdf()
    {
        return view('settings.pdf');
    }

    public function updatePdf(Request $request)
    {
        $request->validate([
            'pdf_font_size' => 'required|integer|min:8|max:20',
            'pdf_font_family' => 'required|string',
            'pdf_accent_color' => 'required|string',
            'pdf_margin_top' => 'required|integer|min:0',
            'pdf_margin_bottom' => 'required|integer|min:0',
            'pdf_margin_left' => 'required|integer|min:0',
            'pdf_margin_right' => 'required|integer|min:0',
            'pdf_header' => 'nullable|string',
            'pdf_footer' => 'nullable|string',
            'pdf_custom_notes' => 'nullable|string',
        ]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::clearCache();

        AuditLog::log('updated', 'settings', 'pdf', null, $request->all());

        return back()->with('success', 'PDF settings updated successfully!');
    }
}

