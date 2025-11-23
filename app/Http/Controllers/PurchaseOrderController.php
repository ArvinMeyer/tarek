<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Invoice;
use App\Models\AuditLog;
use App\Services\PdfService;
use App\Services\EmailService;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['invoice', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $invoice = null;
        if (request('from_invoice')) {
            $invoice = Invoice::with('items')->findOrFail(request('from_invoice'));
        }
        
        return view('purchase-orders.create', compact('invoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'supplier_email' => 'nullable|email',
            'supplier_phone' => 'nullable|string',
            'supplier_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generateNumber(),
                'invoice_id' => $request->invoice_id,
                'created_by' => auth()->id(),
                'supplier_name' => $request->supplier_name,
                'supplier_email' => $request->supplier_email,
                'supplier_phone' => $request->supplier_phone,
                'supplier_address' => $request->supplier_address,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $index => $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'sort_order' => $index,
                ]);
            }

            AuditLog::log('created', 'purchase_order', $po->id, null, $po->toArray());

            DB::commit();

            return redirect()->route('purchase-orders.show', $po)
                ->with('success', 'Purchase Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating PO: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['invoice', 'items', 'creator']);
        
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');
        
        return view('purchase-orders.edit', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'supplier_email' => 'nullable|email',
            'supplier_phone' => 'nullable|string',
            'supplier_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'supplier_name' => $request->supplier_name,
                'supplier_email' => $request->supplier_email,
                'supplier_phone' => $request->supplier_phone,
                'supplier_address' => $request->supplier_address,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $purchaseOrder->items()->delete();
            
            foreach ($request->items as $index => $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'sort_order' => $index,
                ]);
            }

            AuditLog::log('updated', 'purchase_order', $purchaseOrder->id, $oldData, $purchaseOrder->fresh()->toArray());

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating PO: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete purchase orders.');
        }

        AuditLog::log('deleted', 'purchase_order', $purchaseOrder->id, $purchaseOrder->toArray(), null);

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully!');
    }

    public function markReceived(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        AuditLog::log('marked_received', 'purchase_order', $purchaseOrder->id);

        return back()->with('success', 'Purchase Order marked as received.');
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder, PdfService $pdfService)
    {
        return $pdfService->downloadPurchaseOrder($purchaseOrder);
    }

    public function sendEmail(Request $request, PurchaseOrder $purchaseOrder, EmailService $emailService)
    {
        $request->validate([
            'message' => 'nullable|string',
        ]);

        if (!$purchaseOrder->supplier_email) {
            return back()->with('error', 'Supplier email is required to send PO.');
        }

        $result = $emailService->sendPurchaseOrderEmail($purchaseOrder, $request->message ?? '');

        if ($result) {
            return back()->with('success', 'Purchase Order sent successfully!');
        }

        return back()->with('error', 'Failed to send PO. Please check email settings.');
    }

    public function createFromInvoice(Invoice $invoice)
    {
        $po = PurchaseOrder::createFromInvoice($invoice);

        AuditLog::log('created_from_invoice', 'purchase_order', $po->id);

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', 'Purchase Order created from invoice!');
    }
}

