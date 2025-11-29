<div id="transactionModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 transition-opacity duration-300">
    <div id="modalContent"
        class="fixed left-0 top-0 h-full bg-white w-[500px] p-6 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl overflow-y-auto">
        
        {{-- Close Button --}}
        <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <x-icons.close />
        </button>

        <h2 class="mt-8 mb-6 text-2xl font-bold text-center">تفاصيل العملية</h2>

        {{-- Loading State --}}
        <x-ui.loading-state id="modalLoading" />

        {{-- Transaction Details --}}
        <div id="transactionDetails" class="hidden">
            <div class="p-6 mb-6 bg-white rounded-lg border border-gray-200 shadow-lg">
                
                {{-- Transaction Type Badge --}}
                <div class="mb-4 text-center">
                    <span id="processTypeBadge"
                        class="inline-block px-4 py-2 text-sm font-semibold rounded-full"></span>
                </div>

                {{-- Amounts Section --}}
                <x-balance.modal-amounts />

                {{-- Transaction Info --}}
                <x-balance.modal-info />

                {{-- Additional Data --}}
                <div id="additionalDataSection" class="hidden p-4 mb-6 bg-sky-50 rounded-lg border border-sky-400">
                    <div class="mb-2 text-sm font-semibold text-gray-600">بيانات إضافية</div>
                    <div id="additionalData" class="text-sm text-gray-700">
                        {{-- Additional data will be loaded here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>