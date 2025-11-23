<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Services\PdfService;
use App\Services\EmailService;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with(['customer', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        return view('quotations.create');
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

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateNumber($request->customer_country),
                'customer_id' => $customer?->id,
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
                'status' => $request->status ?? 'draft',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sort_order' => $index,
                ]);
            }

            $quotation->calculateTotals();

            AuditLog::log('created', 'quotation', $quotation->id, null, $quotation->toArray());

            DB::commit();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'Quotation created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating quotation: ' . $e->getMessage());
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'creator']);
        
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items');
        
        return view('quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
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
        ]);

        DB::beginTransaction();
        try {
            $oldData = $quotation->toArray();

            $quotation->update([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company,
                'customer_country' => $request->customer_country,
                'customer_street' => $request->customer_street,
                'customer_city' => $request->customer_city,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $quotation->items()->delete();
            
            foreach ($request->items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sort_order' => $index,
                ]);
            }

            $quotation->calculateTotals();

            AuditLog::log('updated', 'quotation', $quotation->id, $oldData, $quotation->fresh()->toArray());

            DB::commit();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'Quotation updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating quotation: ' . $e->getMessage());
        }
    }

    public function destroy(Quotation $quotation)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete quotations.');
        }

        AuditLog::log('deleted', 'quotation', $quotation->id, $quotation->toArray(), null);

        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully!');
    }

    public function downloadPdf(Quotation $quotation, PdfService $pdfService)
    {
        return $pdfService->downloadQuotation($quotation);
    }

    public function sendEmail(Request $request, Quotation $quotation, EmailService $emailService)
    {
        $request->validate([
            'message' => 'nullable|string',
        ]);

        if (!$quotation->customer_email) {
            return back()->with('error', 'Customer email is required to send quotation.');
        }

        $result = $emailService->sendQuotationEmail($quotation, $request->message ?? '');

        if ($result) {
            return back()->with('success', 'Quotation sent successfully!');
        }

        return back()->with('error', 'Failed to send quotation. Please check email settings.');
    }

    public function autosave(Request $request)
    {
        // Auto-save draft logic (called via AJAX every 30 seconds)
        return response()->json(['success' => true]);
    }
}

