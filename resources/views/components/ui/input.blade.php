@props(['label' => null, 'name', 'type' => 'text', 'value' => null, 'help' => null])
@php
    $id = $attributes->get('id', $name);
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp
<div class="form-group ui-field">
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($errorKey, $value) }}" {{ $attributes->except('id')->merge(['id' => $id])->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }}>
    @error($errorKey)<span class="invalid-feedback">{{ $message }}</span>@enderror
    @if($help)<small class="form-text text-muted">{{ $help }}</small>@endif
</div>
