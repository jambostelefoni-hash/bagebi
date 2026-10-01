@props(['eyebrow' => null, 'title', 'description' => null])
<header {{ $attributes->class(['content-header', 'modern-page-header', 'ui-page-header']) }}>
    <div class="ui-page-header__row page-heading-row">
        <div class="ui-page-header__copy">
            @if($eyebrow)<span class="dashboard-eyebrow ui-page-header__eyebrow">{{ $eyebrow }}</span>@endif
            <h1>{{ $title }}</h1>
            @if($description)<p>{{ $description }}</p>@endif
        </div>
        @isset($actions)<div class="page-actions ui-page-header__actions">{{ $actions }}</div>@endisset
    </div>
</header>
