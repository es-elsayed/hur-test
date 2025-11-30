@props([
    'totalBalance' => '0.00',
    'memberType' => null,
    'description' => 'ينتهي أرباحك عن المشاريع المكتملة التي تضيا والتي لم يمر عليها مدة 3 أيام كاملة من إجمالة استلام الأرباح'
])

<div class="p-6 mb-6 bg-white rounded-lg shadow-lg">
    <div class="flex gap-4 items-center mb-4">
        {{-- Icon --}}
        <div class="w-[10%] flex items-center justify-center">
            <x-icons.wallet />
        </div>

        {{-- Balance Info --}}
        <div class="w-[70%]">
            <div>
                <p class="text-gray-500">إجمالي القيمة</p>
                <h2 id="totalBalance" class="text-2xl font-bold text-gray-800">{{ $totalBalance }}ر.س</h2>
            </div>
            <p class="mb-4 text-sm text-gray-600">{{ $description }}</p>
        </div>

        {{-- Actions --}}
        <div class="w-[20%] flex flex-col gap-3">
            @if($memberType === 'client')
                <x-ui.button id="depositBtn" variant="gradient">
                    إيداع رصيد
                </x-ui.button>
            @endif
            
            <x-ui.button id="withdrawBtn" variant="primary">
                سحب الرصيد
            </x-ui.button>
        </div>
    </div>
</div>