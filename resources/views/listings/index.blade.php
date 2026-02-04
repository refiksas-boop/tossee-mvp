@extends('layouts.app')

@section('title', 'Visi skelbimai - ieškau.lt')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold mb-8">Visi skelbimai</h1>

    @if($listings->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Skelbimų nerasta</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($listings as $listing)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <div class="h-40 bg-gray-300 flex items-center justify-center">
                        <span class="text-gray-500">Nuotrauka</span>
                    </div>
                    <div class="p-4">
                        <span class="text-xs text-blue-600 font-semibold">{{ $listing->category->name }}</span>
                        <h3 class="font-semibold mt-2 mb-1">{{ Str::limit($listing->title, 40) }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ $listing->location->name ?? 'Lietuva' }}</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-blue-600">
                                @if($listing->price)
                                    {{ number_format($listing->price, 0, ',', ' ') }} €
                                @else
                                    Susitarti
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $listings->links() }}
        </div>
    @endif
</div>
@endsection
