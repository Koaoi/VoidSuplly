@if ($paginator->hasPages())
    <div class="flex justify-center gap-1 flex-wrap">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-muted text-sm cursor-not-allowed">
                &laquo;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" 
               class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-gray hover:border-void-accent hover:text-void-accent transition-all text-sm">
                &laquo;
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-muted text-sm">
                    {{ $element }}
                </span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 rounded-lg bg-white text-black text-sm font-bold">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" 
                           class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-gray hover:border-void-accent hover:text-void-accent transition-all text-sm">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" 
               class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-gray hover:border-void-accent hover:text-void-accent transition-all text-sm">
                &raquo;
            </a>
        @else
            <span class="px-3 py-2 rounded-lg bg-void-card border border-void-border text-void-muted text-sm cursor-not-allowed">
                &raquo;
            </span>
        @endif
    </div>
@endif