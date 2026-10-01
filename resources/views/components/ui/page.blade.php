@props(['reading' => false])
<section {{ $attributes->class(['content', 'ui-page', 'ui-page--reading' => $reading]) }}>
    {{ $slot }}
</section>
