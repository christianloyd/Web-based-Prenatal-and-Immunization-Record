{{--
    Vaccine Lot Add Modal partial
    Used in: midwife/vaccines/index.blade.php
--}}
<div id="addLotModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
     onclick="if(event.target===this) closeAddLotModal()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary-dark to-primary">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-boxes text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Add Vaccine Lot</h3>
            </div>
            <button onclick="closeAddLotModal()" class="text-white hover:text-header-color">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="addLotForm" onsubmit="submitAddLot(event)" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number <span class="text-red-500">*</span></label>
                    <input type="text" name="lot_number" required placeholder="e.g. LOT-2026-001"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" type="text" name="expiry_date" required placeholder="Choose your date"
                               class="w-full pl-10 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Qty Received <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity_received" min="1" required placeholder="0"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Low Stock Alert Threshold <span class="text-red-500">*</span></label>
                    <input type="number" name="low_stock_threshold" min="1" value="5" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date Received</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" type="text" name="received_date" placeholder="Choose your date"
                               class="w-full pl-10 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplier</label>
                    <input type="text" name="supplier" placeholder="Supplier name"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes..."
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeAddLotModal()"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="addLotSubmitBtn"
                        class="px-5 py-2 text-sm font-semibold bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors shadow-sm">
                    <i class="fas fa-save mr-1"></i> Save Lot
                </button>
            </div>
        </form>
    </div>
</div>
