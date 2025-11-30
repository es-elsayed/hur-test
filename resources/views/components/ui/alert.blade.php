@props([
    'type' => 'success', // success, error, info, warning
    'message' => '',
    'dismissible' => true,
])

@php
    $colors = [
        'success' => 'bg-green-50 border-green-500 text-green-800',
        'error' => 'bg-red-50 border-red-500 text-red-800',
        'info' => 'bg-blue-50 border-blue-500 text-blue-800',
        'warning' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
    ];
    
    $icons = [
        'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    ];
    
    $colorClass = $colors[$type] ?? $colors['info'];
    $iconPath = $icons[$type] ?? $icons['info'];
@endphp

<div 
    id="alertNotification"
    class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md"
    role="alert"
>
    <div class="flex items-center gap-3 p-4 border-r-4 rounded-lg shadow-lg {{ $colorClass }} animate-slide-down">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
        </svg>
        
        <div class="flex-1">
            <p id="alertMessage" class="font-medium">{{ $message }}</p>
        </div>
        
        @if($dismissible)
        <button 
            type="button" 
            onclick="dismissAlert()"
            class="flex-shrink-0 hover:opacity-70 transition-opacity"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @endif
    </div>
</div>

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translate(-50%, -100%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        to {
            opacity: 0;
            transform: translate(-50%, -100%);
        }
    }
    
    .animate-slide-down {
        animation: slideDown 0.3s ease-out forwards;
    }
    
    .animate-slide-up {
        animation: slideUp 0.3s ease-out forwards;
    }
</style>

<script>
    let alertTimeout;

    function showAlert(message, type = 'success', duration = 7000) {
        const alertEl = document.getElementById('alertNotification');
        const messageEl = document.getElementById('alertMessage');
        const containerEl = alertEl.querySelector('div');
        
        // Clear any existing timeout
        if (alertTimeout) {
            clearTimeout(alertTimeout);
        }
        
        // Update message
        messageEl.textContent = message;
        
        // Update colors based on type
        const colors = {
            'success': 'bg-green-50 border-green-500 text-green-800',
            'error': 'bg-red-50 border-red-500 text-red-800',
            'info': 'bg-blue-50 border-blue-500 text-blue-800',
            'warning': 'bg-yellow-50 border-yellow-500 text-yellow-800',
        };
        
        const icons = {
            'success': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'error': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'info': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        };
        
        // Remove all color classes
        Object.values(colors).forEach(colorClass => {
            colorClass.split(' ').forEach(cls => containerEl.classList.remove(cls));
        });
        
        // Add new color classes
        const colorClass = colors[type] || colors['info'];
        colorClass.split(' ').forEach(cls => containerEl.classList.add(cls));
        
        // Update icon
        const iconPath = icons[type] || icons['info'];
        const svgPath = containerEl.querySelector('svg path');
        if (svgPath) {
            svgPath.setAttribute('d', iconPath);
        }
        
        // Show alert
        alertEl.classList.remove('hidden');
        containerEl.classList.remove('animate-slide-up');
        containerEl.classList.add('animate-slide-down');
        
        // Auto hide after duration
        alertTimeout = setTimeout(() => {
            dismissAlert();
        }, duration);
    }
    
    function dismissAlert() {
        const alertEl = document.getElementById('alertNotification');
        const containerEl = alertEl.querySelector('div');
        
        containerEl.classList.remove('animate-slide-down');
        containerEl.classList.add('animate-slide-up');
        
        setTimeout(() => {
            alertEl.classList.add('hidden');
        }, 300);
        
        if (alertTimeout) {
            clearTimeout(alertTimeout);
        }
    }
</script>



