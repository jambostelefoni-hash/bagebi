@props(['icon' => 'fas fa-inbox', 'title', 'description' => null])
<div {{ $attributes->class(['empty-state', 'ui-empty-state']) }}><i class="{{ $icon }}" aria-hidden="true"></i><h3>{{ $title }}</h3>@if($description)<p>{{ $description }}</p>@endif @isset($action)<div class="ui-empty-state__action">{{ $action }}</div>@endisset</div>
