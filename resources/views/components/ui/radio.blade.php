@props(['label', 'name', 'value', 'checked' => false, 'help' => null])
@php($id = $attributes->get('id', $name.'-'.$value))
<div class="ui-choice">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes->except('id')->merge(['id' => $id]) }}>
    <label for="{{ $id }}"><span>{{ $label }}</span>@if($help)<small>{{ $help }}</small>@endif</label>
</div>
