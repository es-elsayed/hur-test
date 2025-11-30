@props(['projects' => []])

<div id="depositModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 transition-opacity duration-300">
    <div id="depositModalContent"
        class="fixed left-0 top-0 h-full bg-white w-[500px] p-6 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl overflow-y-auto">
        
        {{-- Close Button --}}
        <button id="closeDepositModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <x-icons.close />
        </button>

        <h2 class="mt-8 mb-6 text-2xl font-bold text-center">إيداع رصيد</h2>

        <form id="depositForm" class="space-y-4">
            {{-- Amount --}}
            <div>
                <label for="depositAmount" class="block mb-2 text-sm font-medium text-gray-700">
                    المبلغ (ر.س)
                </label>
                <input 
                    type="number" 
                    id="depositAmount" 
                    name="amount"
                    step="0.01"
                    min="0.01"
                    required
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="أدخل المبلغ"
                >
            </div>

            {{-- Project Selection --}}
            <div>
                <label for="depositProject" class="block mb-2 text-sm font-medium text-gray-700">
                    المشروع
                </label>
                <select 
                    id="depositProject" 
                    name="project_id"
                    required
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">اختر المشروع</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Method --}}
            <div>
                <label for="depositPaymentMethod" class="block mb-2 text-sm font-medium text-gray-700">
                    طريقة الدفع
                </label>
                <select 
                    id="depositPaymentMethod" 
                    name="payment_method"
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="credit_card">بطاقة ائتمان</option>
                    <option value="debit_card">بطاقة مدين</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="mada">مدى</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-4">
                <button 
                    type="submit"
                    class="flex-1 px-4 py-3 font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg hover:from-blue-600 hover:to-indigo-700"
                >
                    تأكيد الإيداع
                </button>
                <button 
                    type="button"
                    id="cancelDeposit"
                    class="flex-1 px-4 py-3 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                >
                    إلغاء
                </button>
            </div>
        </form>

        {{-- Loading State --}}
        <div id="depositLoading" class="hidden mt-4">
            <x-ui.loading-state message="جاري معالجة الإيداع..." />
        </div>
    </div>
</div>



