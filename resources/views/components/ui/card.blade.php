@props(['title' => null, 'description' => null, 'flush' => false])
<article {{ $attributes->class(['card', 'ui-card']) }}>
    @if($title || $description || isset($header) || isset($actions))
        <header class="card-header ui-card__header">
            <div>@if($title)<h2 class="card-title">{{ $title }}</h2>@endif @if($description)<p class="ui-card__description">{{ $description }}</p>@endif @isset($header){{ $header }}@endisset</div>
            @isset($actions)<div class="ui-card__actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['card-body', 'ui-card__body', 'ui-card__body--flush' => $flush])>{{ $slot }}</div>
    @isset($footer)<footer class="card-footer ui-card__footer">{{ $footer }}</footer>@endisset
</article>
