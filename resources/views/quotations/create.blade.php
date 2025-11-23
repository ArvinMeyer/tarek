@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Quotation</h1>
    </div>
    
    <form action="{{ route('quotations.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name *</label>
                    <input type="text" name="customer_name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="customer_email" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                    <input type="text" name="customer_phone" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</label>
                    <input type="text" name="customer_company" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country *</label>
                    <select name="customer_country" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City</label>
                    <input type="text" name="customer_city" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Street Address</label>
                    <textarea name="customer_street" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Items</h2>
            
            <div id="items-container" class="space-y-3">
                <div class="item-row grid grid-cols-12 gap-3">
                    <div class="col-span-4">
                        <input type="text" name="items[0][product_name]" placeholder="Product Name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="items[0][size]" placeholder="Size" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="items[0][quantity]" placeholder="Qty" value="1" min="1" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="col-span-3">
                        <input type="number" name="items[0][price]" placeholder="Price" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="col-span-1 flex items-center">
                        <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-700 hidden">Remove</button>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="addItem()" class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                + Add Item
            </button>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Discount</label>
                    <input type="number" name="discount" step="0.01" min="0" value="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tax</label>
                    <input type="number" name="tax" step="0.01" min="0" value="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('quotations.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" name="status" value="draft" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Save Draft
            </button>
            <button type="submit" name="status" value="sent" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save & Send
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = 1;

function addItem() {
    const container = document.getElementById('items-container');
    const newItem = `
        <div class="item-row grid grid-cols-12 gap-3">
            <div class="col-span-4">
                <input type="text" name="items[${itemIndex}][product_name]" placeholder="Product Name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="col-span-2">
                <input type="text" name="items[${itemIndex}][size]" placeholder="Size" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${itemIndex}][quantity]" placeholder="Qty" value="1" min="1" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${itemIndex}][price]" placeholder="Price" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="col-span-1 flex items-center">
                <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-700">Remove</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItem);
    itemIndex++;
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
}

// Auto-save every 30 seconds
setInterval(() => {
    console.log('Auto-saving draft...');
    // Implement AJAX auto-save here
}, 30000);
</script>
@endpush
@endsection

