@props(['label' => null, 'name', 'value' => null, 'help' => null])
@php
    $id = $attributes->get('id', $name);
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp
<div class="form-group ui-field">
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <textarea name="{{ $name }}" {{ $attributes->except('id')->merge(['id' => $id])->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }}>{{ old($errorKey, $value) }}</textarea>
    @error($errorKey)<span class="invalid-feedback">{{ $message }}</span>@enderror
    @if($help)<small class="form-text text-muted">{{ $help }}</small>@endif
</div>
