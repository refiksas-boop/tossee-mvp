@extends('layouts.app')

@section('title', 'Mano paskyra - ieškau.lt')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">Mano paskyra</h1>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['total_listings'] }}</div>
            <div class="text-gray-600">Iš viso skelbimų</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-green-600">{{ $stats['active_listings'] }}</div>
            <div class="text-gray-600">Aktyvūs</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_listings'] }}</div>
            <div class="text-gray-600">Laukia patvirtinimo</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-purple-600">{{ $stats['total_views'] }}</div>
            <div class="text-gray-600">Peržiūros</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Naujausi skelbimai</h2>
            <a href="{{ route('dashboard.listings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Naujas skelbimas
            </a>
        </div>

        @if($recentListings->isEmpty())
            <p class="text-gray-500">Dar neturite skelbimų</p>
        @else
            <div class="space-y-4">
                @foreach($recentListings as $listing)
                    <div class="border-b pb-4">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="font-semibold">{{ $listing->title }}</h3>
                                <p class="text-sm text-gray-600">{{ $listing->category->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($listing->status === 'active') bg-green-100 text-green-800
                                    @elseif($listing->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($listing->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
