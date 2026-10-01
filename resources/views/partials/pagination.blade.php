@if ($paginator->hasPages())
<nav class="platform-pagination" aria-label="გვერდებზე გადასვლა">
    <span class="platform-pagination__summary">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / სულ {{ $paginator->total() }}</span>
    <ul class="pagination">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if ($paginator->onFirstPage())<span class="page-link" aria-disabled="true">წინა</span>
            @else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">წინა</a>@endif
        </li>
        @foreach ($elements as $element)
            @if (is_string($element))<li class="page-item pagination-gap"><span class="page-link">{{ $element }}</span></li>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link" aria-current="page" aria-label="გვერდი {{ $page }}">{{ $page }}</span></li>
                    @else
                        <li class="page-item pagination-number"><a class="page-link" href="{{ $url }}" aria-label="გვერდი {{ $page }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if ($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">შემდეგი</a>
            @else<span class="page-link" aria-disabled="true">შემდეგი</span>@endif
        </li>
    </ul>
</nav>
@endif
