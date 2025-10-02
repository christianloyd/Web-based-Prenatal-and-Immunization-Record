{{-- Reschedule Modal Partial --}}
{{-- This partial contains the JavaScript functions for the reschedule modal --}}
{{-- Usage: @include('partials.midwife.prenatalcheckup.reschedule_modal') --}}

<script>
    /**
     * Open reschedule modal for missed prenatal checkups
     * @param {number} checkupId - The ID of the checkup to reschedule
     */
    function openRescheduleModal(checkupId) {
        // Get CSRF token
        const csrfToken = '{{ csrf_token() }}';
        // Get base URL for proper routing
        const rescheduleUrl = '{{ url("midwife/prenatalcheckup") }}/' + checkupId + '/reschedule';

        // Create modal HTML
        const modalHtml = `
            <div id="rescheduleModal" class="modal-overlay fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeRescheduleModal(event)">
                <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6" onclick="event.stopPropagation()">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Reschedule Missed Checkup</h2>
                        <button type="button" onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="rescheduleForm" method="POST" action="${rescheduleUrl}" class="space-y-4">
                        <input type="hidden" name="_token" value="${csrfToken}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Checkup Date *</label>
                                <input type="date" name="new_checkup_date" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       autocomplete="off">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Checkup Time *</label>
                                <input type="time" name="new_checkup_time" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reschedule Notes</label>
                            <textarea name="reschedule_notes" rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Optional notes about the rescheduling..."></textarea>
                        </div>

                        <!-- Error message container -->
                        <div id="rescheduleError" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" onclick="closeRescheduleModal()"
                                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" id="rescheduleSubmitBtn"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span id="rescheduleSubmitText">
                                    <i class="fas fa-calendar-plus mr-2"></i>Reschedule
                                </span>
                                <span id="rescheduleSubmitLoading" class="hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Rescheduling...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal with animation
        setTimeout(() => {
            document.getElementById('rescheduleModal').classList.add('show');
        }, 10);

        // Add form submission handler
        const form = document.getElementById('rescheduleForm');
        const submitBtn = document.getElementById('rescheduleSubmitBtn');
        const submitText = document.getElementById('rescheduleSubmitText');
        const submitLoading = document.getElementById('rescheduleSubmitLoading');
        const errorDiv = document.getElementById('rescheduleError');

        if (form) {
            form.addEventListener('submit', function(e) {
                // Prevent double submission
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitLoading.classList.remove('hidden');
                errorDiv.classList.add('hidden');

                // Form will submit normally (no e.preventDefault())
                // The loading state will show until page redirects
            });
        }
    }

    /**
     * Close reschedule modal
     * @param {Event} event - The click event (optional)
     */
    function closeRescheduleModal(event) {
        // Only close if clicking the overlay (not the modal content)
        if (event && event.target !== event.currentTarget) {
            return;
        }

        const modal = document.getElementById('rescheduleModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.remove();
                // Re-enable body scrolling if no other modals are open
                if (!document.querySelector('.modal-overlay.show')) {
                    document.body.style.overflow = '';
                }
            }, 300);
        }
    }
</script>