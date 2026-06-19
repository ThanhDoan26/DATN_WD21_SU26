@props([
    'type' => 'button',
    'size' => 'lg',
])

<button
    type="{{ $type }}"
    @class([
        'btn btn-secondary',
        "btn-$size",
    ])
    {{ $attributes }}
>
    {{ $slot }}
</button>
