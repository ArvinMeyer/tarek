@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Welcome back, {{ auth()->user()->username }}!</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Quotations Today</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['quotations_today'] }}</p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Invoices Today</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['invoices_today'] }}</p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unread Emails</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['unread_emails'] }}</p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Overdue Invoices</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['overdue_invoices'] }}</p>
            </div>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Latest Quotations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Quotations</h2>
                <a href="{{ route('quotations.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
            </div>
        </div>
        <div class="p-6">
            @forelse($latestQuotations as $quotation)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                    <a href="{{ route('quotations.show', $quotation) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        {{ $quotation->quotation_number }}
                    </a>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $quotation->customer_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($quotation->total, 2) }}</p>
                    <span class="inline-block px-2 py-1 text-xs rounded-full {{ $quotation->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($quotation->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">No quotations yet</p>
            @endforelse
        </div>
    </div>
    
    <!-- Latest Invoices -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Invoices</h2>
                <a href="{{ route('invoices.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
            </div>
        </div>
        <div class="p-6">
            @forelse($latestInvoices as $invoice)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        {{ $invoice->invoice_number }}
                    </a>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $invoice->customer_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($invoice->total, 2) }}</p>
                    <span class="inline-block px-2 py-1 text-xs rounded-full {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->payment_status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">No invoices yet</p>
            @endforelse
        </div>
    </div>
    
    <!-- Overdue Invoices -->
    @if($overdueInvoices->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-red-600 dark:text-red-400">Overdue Invoices</h2>
            </div>
        </div>
        <div class="p-6">
            @foreach($overdueInvoices as $invoice)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        {{ $invoice->invoice_number }}
                    </a>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Due: {{ $invoice->due_date?->format('M d, Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">${{ number_format($invoice->remaining_balance, 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Recent Emails -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Emails</h2>
                <a href="{{ route('emails.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
            </div>
        </div>
        <div class="p-6">
            @forelse($recentEmails as $email)
            <div class="flex items-start space-x-3 py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="flex-shrink-0">
                    @if(!$email->is_read)
                    <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                    @else
                    <span class="inline-block w-2 h-2 bg-gray-300 rounded-full"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('emails.show', $email) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 truncate block">
                        {{ $email->subject ?: '(No Subject)' }}
                    </a>
                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $email->from_email }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">{{ $email->email_date?->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">No emails yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

