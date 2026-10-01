@props(['label', 'name', 'value' => 1, 'checked' => false, 'help' => null])
@php($id = $attributes->get('id', $name))
<div class="ui-choice">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes->except('id')->merge(['id' => $id]) }}>
    <label for="{{ $id }}"><span>{{ $label }}</span>@if($help)<small>{{ $help }}</small>@endif</label>
</div>
