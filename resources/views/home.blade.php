@extends('layouts.app')

@section('title', 'ieakau.lt - Pagrindinis')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Rask k ieakai!</h1>
            <p class="text-xl mb-8">Universalus skelbims portalas Lietuvoje</p>

            <!-- Search Form -->
            <form action="{{ route('listings.index') }}" method="GET" class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input type="text" name="search" placeholder="K ieakai?"
                               class="w-full px-4 py-3 rounded-md border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select name="category" class="w-full px-4 py-3 rounded-md border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Visos kategorijos</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-md">
                            Ieakoti
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Categories -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-3xl font-bold mb-8 text-center">Populiarios kategorijos</h2>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($categories as $category)
            <a href="{{ route('listings.category', $category->slug) }}"
               class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                <div class="text-4xl mb-3">
                    @switch($category->icon)
                        @case('truck') üöó @break
                        @case('home') üè† @break
                        @case('briefcase') üíº @break
                        @case('shopping-bag') üõçÔ∏è @break
                        @case('user-group') üë• @break
                        @case('heart') ‚ù§Ô∏è @break
                        @default üìã
                    @endswitch
                </div>
                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
            </a>
        @endforeach
    </div>
</div>

<!-- Featured Listings -->
@if($featuredListings->count() > 0)
<div class="bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-8">VIP skelbimai</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredListings as $listing)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <div class="h-48 bg-gray-300 flex items-center justify-center text-gray-500">
                        @if($listing->mainImage)
                            <img src="{{ asset('storage/' . $listing->mainImage->path) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                        @else
                            <span>Nuotrauka</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <span class="text-xs text-blue-600 font-semibold">{{ $listing->category->name }}</span>
                        <h3 class="font-semibold text-lg mt-2 mb-1">{{ Str::limit($listing->title, 50) }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ $listing->location->name ?? 'Lietuva' }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-blue-600">
                                @if($listing->price)
                                    {{ number_format($listing->price, 0, ',', ' ') }} ¨
                                @else
                                    Susitarti
                                @endif
                            </span>
                            <a href="{{ route('listings.show', [$listing->id, $listing->slug]) }}" class="text-blue-600 hover:text-blue-800">
                                Plaiau í
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Latest Listings -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-3xl font-bold mb-8">Naujausi skelbimai</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($latestListings as $listing)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <div class="h-40 bg-gray-300 flex items-center justify-center text-gray-500">
                    @if($listing->mainImage)
                        <img src="{{ asset('storage/' . $listing->mainImage->path) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                    @else
                        <span>Nuotrauka</span>
                    @endif
                </div>
                <div class="p-4">
                    <span class="text-xs text-blue-600 font-semibold">{{ $listing->category->name }}</span>
                    <h3 class="font-semibold mt-2 mb-1">{{ Str::limit($listing->title, 40) }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $listing->location->name ?? 'Lietuva' }}</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-blue-600">
                            @if($listing->price)
                                {{ number_format($listing->price, 0, ',', ' ') }} ¨
                            @else
                                Susitarti
                            @endif
                        </span>
                        <a href="{{ route('listings.show', [$listing->id, $listing->slug]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            Plaiau í
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8 text-gray-500">
                <p>Skelbims dar nra. Bkkite pirmi!</p>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-8">
        <a href="{{ route('listings.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-md">
            }ikrti visus skelbimus
        </a>
    </div>
</div>

<!-- How It Works -->
<div class="bg-blue-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-12 text-center">Kaip veikia ieakau.lt</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-5xl mb-4">=›</div>
                <h3 class="font-semibold text-xl mb-2">1. Sukurk skelbim</h3>
                <p class="text-gray-600">U~registruok paskyr ir paskelbk tai, k parduodi ar siklai</p>
            </div>
            <div class="text-center">
                <div class="text-5xl mb-4">=</div>
                <h3 class="font-semibold text-xl mb-2">2. }mons randa</h3>
                <p class="text-gray-600">Tavo skelbim mato tkkstaniai potencialis pirkjs</p>
            </div>
            <div class="text-center">
                <div class="text-5xl mb-4"></div>
                <h3 class="font-semibold text-xl mb-2">3. Susitark ir parduok</h3>
                <p class="text-gray-600">Susisiek su pirkjais ir u~baik sandor/</p>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('register') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-md">
                Pradti dabar nemokamai
            </a>
        </div>
    </div>
</div>
@endsection
