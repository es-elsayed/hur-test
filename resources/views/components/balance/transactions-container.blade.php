<div class="px-6 pb-4 bg-white rounded-lg shadow-lg">
    <h3 class="p-6 text-xl font-bold text-gray-800">الرصيد</h3>

    {{-- Loading State --}}
    <x-ui.loading-state id="loadingState" />

    {{-- Empty State --}}
    <x-ui.empty-state 
        id="emptyState" 
        message="لا توجد عمليات مالية حتى الآن" 
        class="hidden" 
    />

    {{-- Transactions Table --}}
    <div id="transactionsTable"
        class="hidden overflow-x-auto relative rounded-lg border bg-neutral-primary-soft rounded-base border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <tbody id="transactionsBody">
                {{-- Transactions will be loaded here via Ajax --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div id="paginationContainer" class="flex hidden gap-2 justify-center items-center mt-4">
        {{-- Pagination will be loaded here via Ajax --}}
    </div>
</div>