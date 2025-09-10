<!-- partials/midwife/vaccine/stock_modal.blade.php -->
<div id="stock-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="stock-modal-title"
    onclick="closeStockModal(event)">

    <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="stock-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm0 2h12v10H4V5z"/>
                </svg>
                Stock Management
            </h3>
            <button type="button"
                    onclick="closeStockModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('midwife.vaccines.stock-transaction') }}" 
            method="POST"
            id="stock-form"
            class="space-y-6"
            novalidate>
            @csrf

            <!-- Show server-side validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-medium">Please correct the following errors:</div>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Vaccine *</label>
                <select name="vaccine_id" id="vaccine_id" required onchange="updateStockInfo()"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('vaccine_id') error-border @enderror">
                    <option value="">Choose a vaccine</option>
                    @foreach(\App\Models\Vaccine::orderBy('name')->get() as $vaccine)
                        <option value="{{ $vaccine->id }}">{{ $vaccine->name }} (Current: {{ $vaccine->current_stock }} units)</option>
                    @endforeach
                </select>
                @error('vaccine_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border rounded cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="transaction_type" value="in" required class="mr-3">
                        <div>
                            <div class="font-medium text-primary">Stock In</div>
                            <div class="text-sm text-gray-500">Add inventory</div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="transaction_type" value="out" required class="mr-3">
                        <div>
                            <div class="font-medium text-secondary">Stock Out</div>
                            <div class="text-sm text-gray-500">Remove inventory</div>
                        </div>
                    </label>
                </div>
                @error('transaction_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                <input type="number" name="quantity" id="quantity" min="1" required
                       class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('quantity') error-border @enderror"
                       placeholder="Enter quantity">
                @error('quantity')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason/Notes *</label>
                <textarea name="reason" id="reason" rows="3" required
                          class="form-input w-full border border-gray-300 rounded-lg p-2.5 resize-none focus:ring-2 focus:ring-primary @error('reason') error-border @enderror"
                          placeholder="Reason for stock transaction"></textarea>
                @error('reason')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="currentStockInfo" class="hidden border rounded p-4 bg-blue-50 border-blue-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-primary">Current Stock: <span id="currentStockAmount" class="font-semibold">0</span> units</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t">
                <button type="submit"
                        id="stock-submit-btn"
                        class="btn-primary flex-1 bg-secondary text-white py-2.5 rounded-lg font-semibold hover:bg-charcoal-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Process Transaction
                </button>
                <button type="button"
                        onclick="closeStockModal()"
                        class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>