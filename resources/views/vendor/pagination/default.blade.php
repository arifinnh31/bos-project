@if ($paginator->hasPages())
    <nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="text-sm text-gray-700 leading-5">
                    Showing
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">&lt;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lt;</a>
                    </li>
                @endif

                {{-- Current Page Number --}}
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $paginator->currentPage() }}</span>
                </li>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&gt;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">&gt;</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif