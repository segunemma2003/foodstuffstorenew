<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $blog->title }} - Food Stuff Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="description" content="{{ $blog->excerpt }}">
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #dc2626 0%, #059669 50%, #000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prose {
            color: #374151;
        }
        .prose h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #111827;
        }
        .prose h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        .prose p {
            margin-bottom: 1rem;
            line-height: 1.75;
        }
        .prose ul {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }
        .prose blockquote {
            border-left: 4px solid #10b981;
            padding-left: 1rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Enhanced Header -->
    <header class="relative bg-white shadow-xl border-b-4 border-gradient-to-r from-red-600 via-green-600 to-black">
        <div class="absolute inset-0 bg-gradient-to-r from-red-50 via-green-50 to-gray-50 opacity-30"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <img src="{{asset('mlatest/image/FSSLOGO1-2.png')}}" alt="Food Stuff Store" class="h-16 w-auto rounded-lg shadow-lg">
                        <div class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-500 rounded-full border-2 border-white"></div>
                    </div>
                </div>

                <!-- Enhanced Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="/" class="group relative text-gray-700 hover:text-red-600 font-medium transition-all duration-300">
                        <i class="fas fa-home mr-2"></i>
                        <span>Home</span>
                        <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-red-600 to-green-600 group-hover:w-full transition-all duration-300"></div>
                    </a>
                    <a href="{{ route('blogs.index') }}" class="group relative text-red-600 font-medium transition-all duration-300">
                        <i class="fas fa-blog mr-2"></i>
                        <span>All Blogs</span>
                        <div class="absolute -bottom-1 left-0 w-full h-0.5 bg-gradient-to-r from-red-600 to-green-600"></div>
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>
        <!-- Decorative border -->
        <div class="h-1 bg-gradient-to-r from-red-600 via-green-600 to-black"></div>
    </header>

    <!-- Breadcrumb -->
    <nav class="bg-gradient-to-r from-gray-100 to-gray-50 py-4 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-3 text-sm">
                <a href="/" class="text-gray-600 hover:text-red-600 transition-colors font-medium">
                    <i class="fas fa-home mr-1"></i>Home
                </a>
                <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                <a href="{{ route('blogs.index') }}" class="text-gray-600 hover:text-red-600 transition-colors font-medium">Blog</a>
                <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                <span class="text-gray-900 font-medium">{{ Str::limit($blog->title, 50) }}</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Blog Content -->
            <div class="lg:col-span-2">
                <article class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                    @if($blog->featured_image)
                        <div class="aspect-w-16 aspect-h-9 relative overflow-hidden">
                            <img src="{{ $blog->featured_image_url }}"
                                 alt="{{ $blog->title }}"
                                 class="w-full h-64 md:h-96 object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                    @endif

                    <div class="p-6 md:p-8">
                        <!-- Blog Meta -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-r from-red-500 to-green-500 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                                <span class="font-medium">By {{ $blog->author->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2 text-red-500"></i>
                                <span>{{ $blog->formatted_published_date }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2 text-green-500"></i>
                                <span>{{ $blog->reading_time }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-comments mr-2 text-red-500"></i>
                                <span>{{ $blog->comments_count }} Comments</span>
                            </div>
                        </div>

                        <!-- Blog Title -->
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                            {{ $blog->title }}
                        </h1>

                        <!-- Blog Tags -->
                        @if($blog->tags && count($blog->tags) > 0)
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach($blog->tags as $tag)
                                    <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-red-100 text-gray-800 text-sm rounded-full font-medium border border-green-200">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Blog Content -->
                        <div class="prose prose-lg max-w-none prose-green">
                            {!! $blog->content !!}
                        </div>

                        <!-- Share Buttons -->
                        <div class="border-t border-gray-200 pt-8 mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-share-alt text-red-600 mr-2"></i>
                                Share this article
                            </h3>
                            <div class="flex flex-wrap gap-4">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                                   target="_blank"
                                   class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105">
                                    <i class="fab fa-facebook-f mr-2"></i>
                                    Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($blog->title) }}"
                                   target="_blank"
                                   class="flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-all duration-300 transform hover:scale-105">
                                    <i class="fab fa-twitter mr-2"></i>
                                    Twitter
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($blog->title . ' - ' . request()->fullUrl()) }}"
                                   target="_blank"
                                   class="flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all duration-300 transform hover:scale-105">
                                    <i class="fab fa-whatsapp mr-2"></i>
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Comments Section -->
                <section class="bg-white rounded-2xl shadow-lg mt-8 p-6 md:p-8 border border-gray-100">
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                <i class="fas fa-comments text-red-600 mr-3"></i>
                                Comments ({{ $blog->comments_count }})
                            </h3>
                            @if($comments->count() > 0)
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-filter mr-2"></i>
                                    Showing approved comments
                                </div>
                            @endif
                        </div>

                        <!-- Comment Form -->
                        <div class="mb-8 p-6 bg-gradient-to-r from-gray-50 to-red-50 rounded-xl border border-red-100">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-pen text-red-600 mr-2"></i>
                                Leave a Comment
                            </h4>

                            @if(session('success'))
                                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ session('success') }}
                                    </div>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ session('error') }}
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('blogs.comments.store', $blog->slug) }}" method="POST" class="space-y-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-user mr-1"></i>Name
                                        </label>
                                        <input type="text"
                                               id="name"
                                               name="name"
                                               value="{{ old('name') }}"
                                               placeholder="Your full name"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('name') border-red-500 bg-red-50 @enderror">
                                        @error('name')
                                            <p class="text-red-500 text-sm mt-1 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-envelope mr-1"></i>Email
                                        </label>
                                        <input type="email"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               placeholder="your.email@example.com"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('email') border-red-500 bg-red-50 @enderror">
                                        @error('email')
                                            <p class="text-red-500 text-sm mt-1 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                                <div>
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment mr-1"></i>Comment
                                    </label>
                                    <textarea id="comment"
                                              name="comment"
                                              rows="4"
                                              placeholder="Share your thoughts about this article..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none @error('comment') border-red-500 bg-red-50 @enderror">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <p class="text-red-500 text-sm mt-1 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    <div class="mt-2 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Your comment will be reviewed before being published.
                                    </div>
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white font-medium rounded-lg hover:from-red-700 hover:to-green-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Submit Comment
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Comments List -->
                    @if($comments->count() > 0)
                        <div class="space-y-6">
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                                    <i class="fas fa-messages text-green-600 mr-2"></i>
                                    Reader Comments
                                </h4>

                                @foreach($comments as $comment)
                                    <div class="border-l-4 border-red-500 pl-6 py-6 mb-6 bg-gradient-to-r from-gray-50 to-green-50 rounded-r-xl transition-all duration-300 hover:shadow-md">
                                        <div class="flex items-start space-x-4">
                                            <!-- Avatar -->
                                            <div class="flex-shrink-0">
                                                <img src="{{ $comment->gravatar_url }}"
                                                     alt="{{ $comment->name }}"
                                                     class="w-12 h-12 rounded-full ring-2 ring-red-200 shadow-md">
                                            </div>

                                            <!-- Comment Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div>
                                                        <h5 class="font-semibold text-gray-900 text-lg">{{ $comment->name }}</h5>
                                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            <time datetime="{{ $comment->created_at->toISOString() }}">
                                                                {{ $comment->formatted_date }}
                                                            </time>
                                                            <span class="mx-2">•</span>
                                                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                Verified
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Comment Actions -->
                                                    <div class="flex items-center space-x-2">
                                                        <button onclick="likeComment({{ $comment->id }})"
                                                                class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50"
                                                                title="Like this comment">
                                                            <i class="fas fa-heart text-sm"></i>
                                                        </button>
                                                        <button onclick="shareComment({{ $comment->id }})"
                                                                class="text-gray-400 hover:text-green-500 transition-colors p-2 rounded-full hover:bg-green-50"
                                                                title="Share this comment">
                                                            <i class="fas fa-share text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Comment Text -->
                                                <div class="prose prose-sm max-w-none">
                                                    <p class="text-gray-700 leading-relaxed text-base">{{ $comment->comment }}</p>
                                                </div>

                                                <!-- Comment Meta -->
                                                <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between">
                                                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                                                        <span class="flex items-center cursor-pointer hover:text-red-600">
                                                            <i class="fas fa-reply mr-1"></i>
                                                            Reply
                                                        </span>
                                                        <span class="flex items-center cursor-pointer hover:text-red-600">
                                                            <i class="fas fa-flag mr-1"></i>
                                                            Report
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        Comment #{{ $comment->id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Comments Pagination -->
                        @if($comments->hasPages())
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <div class="bg-gradient-to-r from-gray-50 to-red-50 rounded-xl p-6">
                                    <h5 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-list mr-2 text-red-600"></i>
                                        Browse Comments
                                    </h5>

                                    <nav class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <p class="text-sm text-gray-600 font-medium">
                                                Showing <span class="font-bold text-red-600">{{ $comments->firstItem() }}</span>
                                                to <span class="font-bold text-red-600">{{ $comments->lastItem() }}</span>
                                                of <span class="font-bold text-black">{{ $comments->total() }}</span> comments
                                            </p>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            {{-- Previous Page Link --}}
                                            @if ($comments->onFirstPage())
                                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 cursor-default rounded-lg">
                                                    <i class="fas fa-chevron-left mr-2"></i>
                                                    Previous
                                                </span>
                                            @else
                                                <a href="{{ $comments->previousPageUrl() }}"
                                                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-green-600 border border-transparent rounded-lg hover:from-red-700 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                                                    <i class="fas fa-chevron-left mr-2"></i>
                                                    Previous
                                                </a>
                                            @endif

                                            {{-- Page Numbers --}}
                                            @foreach ($comments->getUrlRange(max(1, $comments->currentPage() - 2), min($comments->lastPage(), $comments->currentPage() + 2)) as $page => $url)
                                                @if ($page == $comments->currentPage())
                                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-green-600 border border-transparent rounded-lg shadow-lg">{{ $page }}</span>
                                                @else
                                                    <a href="{{ $url }}"
                                                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 hover:border-red-300 transition-all duration-300">{{ $page }}</a>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
                                            @if ($comments->hasMorePages())
                                                <a href="{{ $comments->nextPageUrl() }}"
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
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12 bg-gradient-to-r from-gray-50 to-red-50 rounded-xl border-2 border-dashed border-gray-200">
                            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-red-100 to-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-comments text-gray-400 text-3xl"></i>
                            </div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">No comments yet</h4>
                            <p class="text-gray-600 max-w-md mx-auto">Be the first to share your thoughts about this article! Your comment will help start a meaningful discussion.</p>
                        </div>
                    @endif
                </section>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Related Posts -->
                @if($relatedBlogs->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-newspaper text-red-600 mr-2"></i>
                            Related Articles
                        </h3>
                        <div class="space-y-6">
                            @foreach($relatedBlogs as $relatedBlog)
                                <article class="flex space-x-4 p-4 rounded-lg hover:bg-gray-50 transition-colors">
                                    @if($relatedBlog->featured_image)
                                        <img src="{{ $relatedBlog->featured_image_url }}"
                                             alt="{{ $relatedBlog->title }}"
                                             class="w-20 h-20 object-cover rounded-lg shadow-md">
                                    @else
                                        <div class="w-20 h-20 bg-gradient-to-br from-red-400 to-green-600 rounded-lg flex items-center justify-center shadow-md">
                                            <i class="fas fa-leaf text-white text-lg"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 line-clamp-2 mb-2 hover:text-red-600 transition-colors">
                                            <a href="{{ route('blogs.show', $relatedBlog->slug) }}">
                                                {{ $relatedBlog->title }}
                                            </a>
                                        </h4>
                                        <p class="text-sm text-gray-500 flex items-center">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ $relatedBlog->formatted_published_date }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Newsletter Signup -->
                <div class="bg-gradient-to-br from-red-600 via-black to-green-600 text-white rounded-2xl p-6 shadow-lg">
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-envelope text-green-300 mr-2"></i>
                        Stay Updated
                    </h3>
                    <p class="text-gray-100 mb-6">Subscribe to our newsletter for the latest organic food tips and recipes.</p>
                    <form class="space-y-4">
                        <input type="email"
                               placeholder="Enter your email"
                               class="w-full px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-300 border-0">
                        <button type="submit"
                                class="w-full px-4 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-100 transition-colors transform hover:scale-105">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Subscribe Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-gray-900 text-white py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-store text-red-500 mr-2"></i>
                        Food Stuff Store
                    </h3>
                    <p class="text-gray-400 leading-relaxed">Your trusted source for organic, raw food products from farms worldwide. Fresh, healthy, and sustainable.</p>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4 text-green-400">Quick Links</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="/" class="hover:text-white transition-colors flex items-center"><i class="fas fa-home mr-2"></i>Home</a></li>
                        <li><a href="{{ route('blogs.index') }}" class="hover:text-white transition-colors flex items-center"><i class="fas fa-blog mr-2"></i>Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-shopping-cart mr-2"></i>Products</a></li>
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-info-circle mr-2"></i>About Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4 text-green-400">Categories</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-leaf mr-2"></i>Organic Vegetables</a></li>
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-apple-alt mr-2"></i>Fresh Fruits</a></li>
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-seedling mr-2"></i>Raw Foods</a></li>
                        <li><a href="#" class="hover:text-white transition-colors flex items-center"><i class="fas fa-utensils mr-2"></i>Healthy Recipes</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4 text-green-400">Contact Info</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-red-500"></i>
                            <span></span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-red-500"></i>
                            <span>info@foodstuffstore.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-red-500"></i>
                            <span>Lagos, Nigeria</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">&copy; 2025 Food Stuff Store. All rights reserved.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors transform hover:scale-110">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors transform hover:scale-110">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors transform hover:scale-110">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors transform hover:scale-110">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function likeComment(commentId) {
            // Add like functionality here
            console.log('Liked comment:', commentId);
            // You can implement AJAX call to like/unlike comments
        }

        function shareComment(commentId) {
            // Add share functionality here
            console.log('Shared comment:', commentId);
            // You can implement sharing functionality
        }

        // Smooth scroll for comment replies
        document.addEventListener('DOMContentLoaded', function() {
            const replyLinks = document.querySelectorAll('[data-comment-reply]');
            replyLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('#comment').focus();
                });
            });
        });
    </script>
</body>
</html>
