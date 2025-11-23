<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Email;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Services\ImapService;
use App\Services\EmailService;

class EmailController extends Controller
{
    public function index()
    {
        $emails = Email::with(['customer', 'assignedUser'])
            ->where('is_sent', false)
            ->orderBy('email_date', 'desc')
            ->paginate(20);
        
        $unreadCount = Email::where('is_read', false)->where('is_sent', false)->count();
        
        return view('emails.index', compact('emails', 'unreadCount'));
    }

    public function show(Email $email)
    {
        $email->load(['customer', 'attachments', 'assignedUser']);
        
        // Mark as read
        $email->markAsRead();
        
        return view('emails.show', compact('email'));
    }

    public function fetch(ImapService $imapService)
    {
        try {
            $imapService->fetchEmails(50);
            
            return back()->with('success', 'Emails fetched successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch emails: ' . $e->getMessage());
        }
    }

    public function markAsRead(Email $email, ImapService $imapService)
    {
        $imapService->markAsRead($email);
        
        return back()->with('success', 'Email marked as read.');
    }

    public function markAsUnread(Email $email, ImapService $imapService)
    {
        $imapService->markAsUnread($email);
        
        return back()->with('success', 'Email marked as unread.');
    }

    public function assign(Request $request, Email $email)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $email->update(['assigned_to' => $request->user_id]);

        AuditLog::log('assigned', 'email', $email->id);

        return back()->with('success', 'Email assigned successfully!');
    }

    public function compose()
    {
        $customers = Customer::orderBy('name')->get();
        
        return view('emails.compose', compact('customers'));
    }

    public function send(Request $request, EmailService $emailService)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $attachments = [];
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('temp_attachments');
                $attachments[] = storage_path('app/' . $path);
            }
        }

        $result = $emailService->sendEmail(
            $request->to,
            $request->subject,
            $request->body,
            $attachments
        );

        // Clean up temp files
        foreach ($attachments as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        if ($result) {
            AuditLog::log('sent', 'email', null);
            
            return redirect()->route('emails.index')
                ->with('success', 'Email sent successfully!');
        }

        return back()->with('error', 'Failed to send email. Please check settings.');
    }

    public function reply(Request $request, Email $email, EmailService $emailService)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $subject = 'Re: ' . $email->subject;
        $body = $request->body . "\n\n--- Original Message ---\n" . $email->body_text;

        $result = $emailService->sendEmail(
            $email->from_email,
            $subject,
            $body
        );

        if ($result) {
            AuditLog::log('replied', 'email', $email->id);
            
            return back()->with('success', 'Reply sent successfully!');
        }

        return back()->with('error', 'Failed to send reply.');
    }

    public function forward(Request $request, Email $email, EmailService $emailService)
    {
        $request->validate([
            'to' => 'required|email',
            'body' => 'nullable|string',
        ]);

        $subject = 'Fwd: ' . $email->subject;
        $body = ($request->body ?? '') . "\n\n--- Forwarded Message ---\n" . $email->body_text;

        $result = $emailService->sendEmail(
            $request->to,
            $subject,
            $body
        );

        if ($result) {
            AuditLog::log('forwarded', 'email', $email->id);
            
            return back()->with('success', 'Email forwarded successfully!');
        }

        return back()->with('error', 'Failed to forward email.');
    }

    public function linkToCustomer(Request $request, Email $email)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $email->update(['customer_id' => $request->customer_id]);

        AuditLog::log('linked_to_customer', 'email', $email->id);

        return back()->with('success', 'Email linked to customer!');
    }
}

