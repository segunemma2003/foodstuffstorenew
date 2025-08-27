{{-- resources/views/pagination/custom.blade.php --}}
@if ($paginator->hasPages())
    <nav class="flex items-center justify-between bg-white px-6 py-4 rounded-xl shadow-lg border border-gray-100">
        <div class="flex items-center">
            <p class="text-sm text-gray-600 font-medium">
                Showing <span class="font-bold text-red-600">{{ $paginator->firstItem() }}</span>
                to <span class="font-bold text-red-600">{{ $paginator->lastItem() }}</span>
                of <span class="font-bold text-black">{{ $paginator->total() }}</span> results
            </p>
        </div>

        <div class="flex items-center space-x-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-default rounded-lg">
                    <i class="fas fa-chevron-left mr-2"></i>
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-green-600 border border-transparent rounded-lg hover:from-red-700 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-chevron-left mr-2"></i>
                    Previous
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default rounded-lg">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-green-600 border border-transparent rounded-lg shadow-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 hover:border-red-300 transition-all duration-300">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-green-600 border border-transparent rounded-lg hover:from-red-700 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                    Next
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-default rounded-lg">
                    Next
                    <i class="fas fa-chevron-right ml-2"></i>
                </span>
            @endif
        </div>
    </nav>
@endif

<style>
    /* Enhanced pagination styles */
    nav[aria-label="pagination"] {
        margin-top: 2rem;
    }

    /* Smooth transitions for all pagination elements */
    nav a, nav span {
        transition: all 0.3s ease;
    }

    /* Hover effects for page numbers */
    nav a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Active page styling */
    nav .bg-gradient-to-r {
        background: linear-gradient(135deg, #dc2626 0%, #059669 100%);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }
</style>
