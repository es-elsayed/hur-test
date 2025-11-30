@props(['projects' => []])

<div id="withdrawModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 transition-opacity duration-300">
    <div id="withdrawModalContent"
        class="fixed left-0 top-0 h-full bg-white w-[500px] p-6 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl overflow-y-auto">
        
        {{-- Close Button --}}
        <button id="closeWithdrawModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <x-icons.close />
        </button>

        <h2 class="mt-8 mb-6 text-2xl font-bold text-center">سحب رصيد</h2>

        <form id="withdrawForm" class="space-y-4">
            {{-- Amount --}}
            <div>
                <label for="withdrawAmount" class="block mb-2 text-sm font-medium text-gray-700">
                    المبلغ (ر.س)
                </label>
                <input 
                    type="number" 
                    id="withdrawAmount" 
                    name="amount"
                    step="0.01"
                    min="0.01"
                    required
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="أدخل المبلغ"
                >
            </div>

            {{-- Project Selection (Optional) --}}
            <div>
                <label for="withdrawProject" class="block mb-2 text-sm font-medium text-gray-700">
                    المشروع (اختياري)
                </label>
                <select 
                    id="withdrawProject" 
                    name="project_id"
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">بدون مشروع</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bank Account --}}
            <div>
                <label for="withdrawBankAccount" class="block mb-2 text-sm font-medium text-gray-700">
                    رقم الحساب البنكي (IBAN)
                </label>
                <input 
                    type="text" 
                    id="withdrawBankAccount" 
                    name="bank_account"
                    required
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="SA0000000000000000000000"
                >
            </div>

            {{-- Bank Name --}}
            <div>
                <label for="withdrawBankName" class="block mb-2 text-sm font-medium text-gray-700">
                    اسم البنك
                </label>
                <select 
                    id="withdrawBankName" 
                    name="bank_name"
                    required
                    class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">اختر البنك</option>
                    <option value="Al Rajhi Bank">مصرف الراجحي</option>
                    <option value="NCB">البنك الأهلي التجاري</option>
                    <option value="Saudi British Bank">البنك السعودي البريطاني</option>
                    <option value="Riyad Bank">بنك الرياض</option>
                    <option value="Al Inma Bank">بنك الإنماء</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-4">
                <button 
                    type="submit"
                    class="flex-1 px-4 py-3 font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg hover:from-blue-600 hover:to-indigo-700"
                >
                    تأكيد السحب
                </button>
                <button 
                    type="button"
                    id="cancelWithdraw"
                    class="flex-1 px-4 py-3 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                >
                    إلغاء
                </button>
            </div>
        </form>

        {{-- Loading State --}}
        <div id="withdrawLoading" class="hidden mt-4">
            <x-ui.loading-state message="جاري معالجة السحب..." />
        </div>
    </div>
</div>



