@props([
    'label' => null,
    'required' => false,
])

<div class="checkbox-wrapper">
    <input
        type="checkbox"
        class="checkbox-input"
        {{ $attributes }}
    />
    
    @if ($label)
        <label for="{{ $attributes->get('id') }}" class="checkbox-label">
            {{ $label }}
            @if ($required)
                <span class="text-red-400">*</span>
            @endif
        </label>
    @endif
</div>

@if ($errors->has($attributes->get('name')))
    <div class="form-error mt-2">
        {{ $errors->first($attributes->get('name')) }}
    </div>
@endif
