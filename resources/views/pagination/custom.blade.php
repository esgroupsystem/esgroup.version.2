@if ($paginator->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100">

        {{-- Showing X to Y of Z results --}}
        <div class="small text-muted mb-2 mb-md-0">
            Showing
            {{ $paginator->firstItem() }}
            to
            {{ $paginator->lastItem() }}
            of
            {{ $paginator->total() }}
            results
        </div>

        {{-- Pagination --}}
        <ul class="pagination pagination-sm mb-0">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
                </li>
            @endif


            {{-- Page Numbers (only 5 around current) --}}
            @php
                $start = max($paginator->currentPage() - 2, 1);
                $end = min($paginator->currentPage() + 2, $paginator->lastPage());
            @endphp

            {{-- First Page --}}
            @if ($start > 1)
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endif

            {{-- Page Range --}}
            @for ($i = $start; $i <= $end; $i++)
                <li class="page-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            {{-- Last Page --}}
            @if ($end < $paginator->lastPage())
                @if ($end < $paginator->lastPage() - 1)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
            @endif


            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif

        </ul>

    </div>
@endif
    