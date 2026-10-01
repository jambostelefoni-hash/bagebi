@props(['label', 'value', 'icon' => null, 'variant' => 'primary'])
<article {{ $attributes->class(['ui-stat-card', 'ui-stat-card--'.$variant]) }}>@if($icon)<span class="ui-stat-card__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>@endif<div><small>{{ $label }}</small><strong>{{ $value }}</strong>@isset($footer)<span>{{ $footer }}</span>@endisset</div></article>
