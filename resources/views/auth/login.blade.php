<x-layouts.app title="تسجيل الدخول - منصة باشنور" :showSidebar="false" :showFooter="false">
    <div class="flex justify-center items-center min-h-screen">
        <div class="w-full max-w-md">
            <div class="p-8 bg-white rounded-lg shadow-lg">
                <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">تسجيل الدخول</h2>

                @if ($errors->any())
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">
                            البريد الإلكتروني
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="example@email.com"
                        >
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                            كلمة المرور
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="mr-2 text-sm text-gray-600">تذكرني</span>
                        </label>
                    </div>

                    <button 
                        type="submit"
                        class="px-4 py-3 w-full font-semibold text-white bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        تسجيل الدخول
                    </button>
                </form>

                <div class="mt-6 text-sm text-center text-gray-600">
                    <p class="mb-2">للتجربة، استخدم أحد الحسابات التالية:</p>
                    <p class="text-xs text-gray-500">البريد: أي بريد من قاعدة البيانات</p>
                    <p class="text-xs text-gray-500">كلمة المرور: password</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

