<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Email;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'quotations_today' => Quotation::whereDate('created_at', $today)->count(),
            'invoices_today' => Invoice::whereDate('created_at', $today)->count(),
            'unread_emails' => Email::where('is_read', false)->where('is_sent', false)->count(),
            'overdue_invoices' => Invoice::where('payment_status', 'overdue')->count(),
        ];

        $latestQuotations = Quotation::with('customer', 'creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $latestInvoices = Invoice::with('customer', 'creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $overdueInvoices = Invoice::where('payment_status', 'overdue')
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $recentEmails = Email::where('is_sent', false)
            ->with('customer')
            ->orderBy('email_date', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'stats',
            'latestQuotations',
            'latestInvoices',
            'overdueInvoices',
            'recentEmails'
        ));
    }
}

