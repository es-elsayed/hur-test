<aside class="bg-white lg:w-64">
    <nav class="py-8">
        @if($member)
        <div class="p-4">
            <div
                class="flex items-center gap-3 border border-gray-[#4b5563] p-3 rounded-lg cursor-pointer hover:bg-gray-50">
                <div class="flex items-center justify-center w-12 h-12 text-lg font-bold text-white rounded-full
                    {{ $member->type === 'client' ? 'bg-blue-500' : 'bg-green-500' }}">
                    {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="text-sm font-bold text-gray-800">{{ $member->name }}</div>
                    <div class="text-sm text-gray-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $member->type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                           {{ $member->type === 'client' ? '@client' : '@freelancer' }}
                        </span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
        @endif
        
        <a href="#" class="flex gap-3 items-center px-4 py-3 text-gray-600 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            الرئيسية
        </a>
        
        <a href="#" class="flex gap-3 items-center px-4 py-3 text-gray-600 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            ملف الأعمال
        </a>
        
        <a href="#" class="flex gap-3 items-center px-4 py-3 text-gray-600 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            الباقات
        </a>
        
        <a href="#" class="flex gap-3 items-center px-4 py-3 text-gray-600 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            العروض المقدمة
        </a>
        
        <a href="#" class="flex gap-3 items-center px-4 py-3 text-gray-600 hover:bg-gray-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            التقييمات
        </a>
        
        <a href="{{ route('balance.index') }}"
            class="flex gap-3 items-center px-4 py-3 {{ request()->routeIs('balance.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            الرصيد
        </a>
    </nav>
</aside>

