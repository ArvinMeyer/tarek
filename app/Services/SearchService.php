<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Email;
use Illuminate\Support\Collection;

class SearchService
{
    public function globalSearch(string $query): array
    {
        $query = trim($query);
        
        if (empty($query)) {
            return [
                'customers' => collect([]),
                'quotations' => collect([]),
                'invoices' => collect([]),
                'purchase_orders' => collect([]),
                'emails' => collect([]),
            ];
        }

        return [
            'customers' => $this->searchCustomers($query),
            'quotations' => $this->searchQuotations($query),
            'invoices' => $this->searchInvoices($query),
            'purchase_orders' => $this->searchPurchaseOrders($query),
            'emails' => $this->searchEmails($query),
        ];
    }

    protected function searchCustomers(string $query): Collection
    {
        return Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('company_name', 'LIKE', "%{$query}%")
            ->with(['quotations', 'invoices', 'emails', 'files'])
            ->limit(20)
            ->get();
    }

    protected function searchQuotations(string $query): Collection
    {
        return Quotation::where('quotation_number', 'LIKE', "%{$query}%")
            ->orWhere('customer_name', 'LIKE', "%{$query}%")
            ->orWhere('customer_email', 'LIKE', "%{$query}%")
            ->orWhere('customer_company', 'LIKE', "%{$query}%")
            ->with(['customer', 'items', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    protected function searchInvoices(string $query): Collection
    {
        return Invoice::where('invoice_number', 'LIKE', "%{$query}%")
            ->orWhere('customer_name', 'LIKE', "%{$query}%")
            ->orWhere('customer_email', 'LIKE', "%{$query}%")
            ->orWhere('customer_company', 'LIKE', "%{$query}%")
            ->with(['customer', 'items', 'creator', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    protected function searchPurchaseOrders(string $query): Collection
    {
        return PurchaseOrder::where('po_number', 'LIKE', "%{$query}%")
            ->orWhere('supplier_name', 'LIKE', "%{$query}%")
            ->orWhere('supplier_email', 'LIKE', "%{$query}%")
            ->with(['invoice', 'items', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    protected function searchEmails(string $query): Collection
    {
        return Email::where('subject', 'LIKE', "%{$query}%")
            ->orWhere('from_email', 'LIKE', "%{$query}%")
            ->orWhere('to_email', 'LIKE', "%{$query}%")
            ->orWhere('body_text', 'LIKE', "%{$query}%")
            ->with(['customer', 'attachments'])
            ->orderBy('email_date', 'desc')
            ->limit(20)
            ->get();
    }

    public function searchByCustomer(Customer $customer): array
    {
        return [
            'quotations' => $customer->quotations()->orderBy('created_at', 'desc')->get(),
            'invoices' => $customer->invoices()->orderBy('created_at', 'desc')->get(),
            'emails' => $customer->emails()->orderBy('email_date', 'desc')->get(),
            'files' => $customer->files()->orderBy('created_at', 'desc')->get(),
        ];
    }
}

