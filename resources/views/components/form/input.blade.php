@props([
    'label' => null,
    'type' => 'text',
    'icon' => null,
    'hint' => null,
    'required' => false,
])

<div class="form-group">
    @if ($label)
        <label for="{{ $attributes->get('id') }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-red-400">*</span>
            @endif
        </label>
    @endif
    
    <div class="form-input-wrapper">
        <input
            type="{{ $type }}"
            @if($type === 'email')
                autocomplete="email"
            @elseif($type === 'password')
                autocomplete="current-password"
            @endif
            class="form-input"
            {{ $attributes }}
        />
        
        @if ($icon)
            <span class="form-input-icon">{{ $icon }}</span>
        @endif
    </div>
    
    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
    
    @if ($errors->has($attributes->get('name')))
        <div class="form-error">
            {{ $errors->first($attributes->get('name')) }}
        </div>
    @endif
</div>
