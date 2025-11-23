<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\AuditLog;
use App\Services\SearchService;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('created_at', 'desc')->paginate(20);
        
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company_name' => 'nullable|string',
            'country' => 'nullable|string',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($request->all());

        AuditLog::log('created', 'customer', $customer->id, null, $customer->toArray());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['quotations', 'invoices', 'emails', 'files']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company_name' => 'nullable|string',
            'country' => 'nullable|string',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $oldData = $customer->toArray();

        $customer->update($request->all());

        AuditLog::log('updated', 'customer', $customer->id, $oldData, $customer->fresh()->toArray());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete customers.');
        }

        AuditLog::log('deleted', 'customer', $customer->id, $customer->toArray(), null);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    public function uploadFile(Request $request, Customer $customer)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('customer_files/' . $customer->id, 'public');

        CustomerFile::create([
            'customer_id' => $customer->id,
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        AuditLog::log('uploaded_file', 'customer', $customer->id);

        return back()->with('success', 'File uploaded successfully!');
    }

    public function deleteFile(CustomerFile $file)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete files.');
        }

        Storage::disk('public')->delete($file->file_path);
        
        AuditLog::log('deleted_file', 'customer', $file->customer_id);
        
        $file->delete();

        return back()->with('success', 'File deleted successfully!');
    }
}

