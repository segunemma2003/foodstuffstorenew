<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Blog - Food Stuff Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-scale:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
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
                        <span>Back Home</span>
                        <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-red-600 to-green-600 group-hover:w-full transition-all duration-300"></div>
                    </a>
                    <div class="flex items-center space-x-4">
                        <div class="h-8 w-px bg-gray-300"></div>
                        <span class="text-sm text-gray-500 font-medium">Blog</span>
                        <div class="h-2 w-2 bg-red-500 rounded-full animate-pulse"></div>
                    </div>
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

    <!-- Enhanced Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-red-600 via-green-600 to-black"></div>
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full mix-blend-multiply filter blur-xl animate-pulse"></div>
            <div class="absolute top-20 right-20 w-24 h-24 bg-green-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse delay-1000"></div>
            <div class="absolute bottom-10 left-1/3 w-20 h-20 bg-red-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse delay-2000"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center text-white">
            <div class="space-y-6">
                <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-medium">
                    <i class="fas fa-blog mr-2"></i>
                    Welcome to our Blog
                </div>

                <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                    Our <span class="text-green-300">Fresh</span> Blog
                </h1>

                <p class="text-xl md:text-2xl text-gray-100 max-w-4xl mx-auto leading-relaxed">
                    Discover fresh insights, organic recipes, and healthy living tips from our farm to your table
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8">
                    <div class="flex items-center text-green-200">
                        <i class="fas fa-leaf mr-2"></i>
                        <span>100% Organic Content</span>
                    </div>
                    <div class="hidden sm:block w-px h-6 bg-white/30"></div>
                    <div class="flex items-center text-green-200">
                        <i class="fas fa-heart mr-2"></i>
                        <span>Health & Wellness</span>
                    </div>
                    <div class="hidden sm:block w-px h-6 bg-white/30"></div>
                    <div class="flex items-center text-green-200">
                        <i class="fas fa-seedling mr-2"></i>
                        <span>Farm Fresh Tips</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave decoration -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" class="w-full h-12 fill-gray-50">
                <path d="M0,64L48,58.7C96,53,192,43,288,48C384,53,480,75,576,80C672,85,768,75,864,64C960,53,1056,43,1152,48C1248,53,1344,75,1392,85.3L1440,96L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Blog Posts Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($blogs->count() > 0)
            <!-- Stats Bar -->
            <div class="mb-12 p-6 glass-effect rounded-2xl border border-white/50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-red-600 mb-2">{{ $blogs->total() }}</div>
                        <div class="text-gray-600 font-medium">Total Articles</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 mb-2">{{ $blogs->sum('views_count') }}</div>
                        <div class="text-gray-600 font-medium">Total Views</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-black mb-2">{{ $blogs->sum('comments_count') }}</div>
                        <div class="text-gray-600 font-medium">Total Comments</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($blogs as $blog)
                    <article class="bg-white rounded-2xl shadow-lg overflow-hidden hover-scale border border-gray-100">
                        @if($blog->featured_image)
                            <div class="relative aspect-w-16 aspect-h-9 overflow-hidden">
                                <img src="{{ $blog->featured_image_url }}"
                                     alt="{{ $blog->title }}"
                                     class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                <div class="absolute top-4 right-4 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-gray-800">
                                    <i class="fas fa-eye mr-1"></i>{{ $blog->views_count }}
                                </div>
                            </div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-red-400 via-green-400 to-black flex items-center justify-center relative overflow-hidden">
                                <div class="absolute inset-0 opacity-20">
                                    <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="7" cy="7" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
                                </div>
                                <i class="fas fa-leaf text-white text-5xl relative z-10"></i>
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-gradient-to-r from-red-500 to-green-500 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                    <span class="font-medium">{{ $blog->author->name }}</span>
                                </div>
                                <span class="mx-3 text-gray-300">•</span>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2 text-red-500"></i>
                                    <span>{{ $blog->formatted_published_date }}</span>
                                </div>
                            </div>

                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 leading-tight">
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-red-600 transition-colors duration-300">
                                    {{ $blog->title }}
                                </a>
                            </h2>

                            <p class="text-gray-600 mb-4 line-clamp-3 leading-relaxed">{{ $blog->excerpt }}</p>

                            @if($blog->tags && count($blog->tags) > 0)
                                <div class="flex flex-wrap gap-2 mb-6">
                                    @foreach(array_slice($blog->tags, 0, 3) as $tag)
                                        <span class="px-3 py-1 bg-gradient-to-r from-green-100 to-red-100 text-gray-800 text-xs rounded-full font-medium border border-green-200">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <a href="{{ route('blogs.show', $blog->slug) }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-green-600 text-white font-medium rounded-lg hover:from-red-700 hover:to-green-700 transition-all duration-300 transform hover:scale-105">
                                    Read More
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>

                                <div class="flex items-center space-x-4 text-gray-500 text-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-1 text-green-500"></i>
                                        <span>{{ $blog->reading_time }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-comments mr-1 text-red-500"></i>
                                        <span>{{ $blog->comments_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Enhanced Pagination -->
            <div class="mt-16">
                {{ $blogs->links('pagination.custom') }}
            </div>
        @else
            <div class="text-center py-20">
                <div class="w-32 h-32 mx-auto mb-8 bg-gradient-to-r from-red-100 to-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-blog text-gray-400 text-5xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-4">No Blog Posts Yet</h3>
                <p class="text-gray-600 text-lg max-w-md mx-auto">Check back soon for fresh content about organic food and healthy living!</p>
            </div>
        @endif
    </main>

    <!-- Enhanced Newsletter Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-red-600 via-black to-green-600"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="white" fill-opacity="0.1"%3E%3Cpath d="M20 20c0 4.4-3.6 8-8 8s-8-3.6-8-8 3.6-8 8-8 8 3.6 8 8zm0-20c0 4.4-3.6 8-8 8s-8-3.6-8-8 3.6-8 8-8 8 3.6 8 8z"/%3E%3C/g%3E%3C/svg%3E')]"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center text-white">
            <div class="mb-8">
                <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-medium mb-6">
                    <i class="fas fa-envelope mr-2"></i>
                    Stay Connected
                </div>
                <h2 class="text-4xl font-bold mb-4">Stay Updated</h2>
                <p class="text-xl text-gray-100 mb-8 max-w-2xl mx-auto">
                    Subscribe to our newsletter for the latest organic food tips, recipes, and exclusive content
                </p>
            </div>

            <form class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                <input type="email"
                       placeholder="Enter your email address"
                       class="flex-1 px-6 py-4 rounded-xl text-gray-900 bg-white/95 backdrop-blur-sm focus:outline-none focus:ring-4 focus:ring-white/30 font-medium">
                <button type="submit"
                        class="px-8 py-4 bg-white text-black font-bold rounded-xl hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Subscribe
                    <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Food Stuff Store</h3>
                    <p class="text-gray-400">Your trusted source for organic, raw food products from farms worldwide.</p>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="/blogs" class="hover:text-white transition-colors">Blog</a></li>

                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Categories</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Organic Vegetables</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Fresh Fruits</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Raw Foods</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Healthy Recipes</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span></span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <span>info@foodstuffstore.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>HQ - Suite B15 Abraham Plaza, Utako, Abuja, Nigeria.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; 2025 Food Stuff Store. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</body>
</html>
