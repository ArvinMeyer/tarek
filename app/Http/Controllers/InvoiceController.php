<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Services\PdfService;
use App\Services\EmailService;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['customer', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $quotation = null;
        if (request('from_quotation')) {
            $quotation = Quotation::with('items')->findOrFail(request('from_quotation'));
        }
        
        return view('invoices.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_company' => 'nullable|string',
            'customer_country' => 'required|in:US,UK,CA',
            'customer_street' => 'nullable|string',
            'customer_city' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'quotation_id' => 'nullable|exists:quotations,id',
        ]);

        DB::beginTransaction();
        try {
            // Find or create customer
            $customer = null;
            if ($request->customer_email) {
                $customer = Customer::firstOrCreate(
                    ['email' => $request->customer_email],
                    [
                        'name' => $request->customer_name,
                        'phone' => $request->customer_phone,
                        'company_name' => $request->customer_company,
                        'country' => $request->customer_country,
                        'street' => $request->customer_street,
                        'city' => $request->customer_city,
                    ]
                );
            }

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber($request->customer_country),
                'customer_id' => $customer?->id,
                'quotation_id' => $request->quotation_id,
                'created_by' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company,
                'customer_country' => $request->customer_country,
                'customer_street' => $request->customer_street,
                'customer_city' => $request->customer_city,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'due_date' => $request->due_date,
                'status' => $request->status ?? 'draft',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sort_order' => $index,
                ]);
            }

            $invoice->calculateTotals();

            // Mark quotation as converted if applicable
            if ($request->quotation_id) {
                Quotation::find($request->quotation_id)->update(['status' => 'converted']);
            }

            AuditLog::log('created', 'invoice', $invoice->id, null, $invoice->toArray());

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'creator', 'payments']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_company' => 'nullable|string',
            'customer_country' => 'required|in:US,UK,CA',
            'customer_street' => 'nullable|string',
            'customer_city' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $invoice->toArray();

            $invoice->update([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company,
                'customer_country' => $request->customer_country,
                'customer_street' => $request->customer_street,
                'customer_city' => $request->customer_city,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            
            foreach ($request->items as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sort_order' => $index,
                ]);
            }

            $invoice->calculateTotals();

            AuditLog::log('updated', 'invoice', $invoice->id, $oldData, $invoice->fresh()->toArray());

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete invoices.');
        }

        AuditLog::log('deleted', 'invoice', $invoice->id, $invoice->toArray(), null);

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    public function hold(Invoice $invoice)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can hold invoices.');
        }

        $invoice->update(['status' => 'hold']);

        AuditLog::log('hold', 'invoice', $invoice->id);

        return back()->with('success', 'Invoice put on hold.');
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'recorded_by' => auth()->id(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        AuditLog::log('payment_added', 'invoice', $invoice->id);

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function downloadPdf(Invoice $invoice, PdfService $pdfService)
    {
        return $pdfService->downloadInvoice($invoice);
    }

    public function sendEmail(Request $request, Invoice $invoice, EmailService $emailService)
    {
        $request->validate([
            'message' => 'nullable|string',
        ]);

        if (!$invoice->customer_email) {
            return back()->with('error', 'Customer email is required to send invoice.');
        }

        $result = $emailService->sendInvoiceEmail($invoice, $request->message ?? '');

        if ($result) {
            return back()->with('success', 'Invoice sent successfully!');
        }

        return back()->with('error', 'Failed to send invoice. Please check email settings.');
    }
}

