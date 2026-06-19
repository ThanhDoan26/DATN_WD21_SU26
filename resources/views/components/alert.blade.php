@props([
    'type' => 'info', // info, success, warning, error
    'title' => null,
    'dismissible' => false,
])

@php
    $typeClasses = [
        'success' => 'alert-success',
        'error' => 'alert-error',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];
@endphp

<div
    @class(['alert', $typeClasses[$type] ?? 'alert-info'])
    {{ $attributes }}
>
    <div class="flex items-start gap-3">
        <div class="flex-1">
            @if ($title)
                <p class="font-medium">{{ $title }}</p>
            @endif
            <p class="text-sm opacity-90">{{ $slot }}</p>
        </div>
        
        @if ($dismissible)
            <button
                type="button"
                class="opacity-75 hover:opacity-100 transition-opacity"
                onclick="this.parentElement.parentElement.remove()"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        @endif
    </div>
</div>
