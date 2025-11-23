@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Quotations</h1>
    @if(auth()->user()->hasPermission('quotations.create'))
    <a href="{{ route('quotations.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        + New Quotation
    </a>
    @endif
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($quotations as $quotation)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('quotations.show', $quotation) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            {{ $quotation->quotation_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $quotation->customer_name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quotation->customer_email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        ${{ number_format($quotation->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $quotation->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($quotation->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                            {{ ucfirst($quotation->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $quotation->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        @if(auth()->user()->hasPermission('quotations.edit'))
                        <a href="{{ route('quotations.edit', $quotation) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        @endif
                        <a href="{{ route('quotations.pdf', $quotation) }}" class="text-blue-600 hover:text-blue-900">PDF</a>
                        @if($quotation->status !== 'converted')
                        <a href="{{ route('invoices.create', ['from_quotation' => $quotation->id]) }}" class="text-green-600 hover:text-green-900">Convert</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No quotations found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $quotations->links() }}
    </div>
</div>
@endsection

