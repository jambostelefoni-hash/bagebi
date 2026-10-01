@props(['name'])
<svg {{ $attributes->class(['admin-icon']) }} aria-hidden="true" focusable="false"><use href="{{ asset('images/admin-icons.svg') }}?v={{ filemtime(public_path('images/admin-icons.svg')) }}#{{ $name }}"></use></svg>
