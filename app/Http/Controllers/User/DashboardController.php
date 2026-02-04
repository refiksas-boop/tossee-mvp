<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [
            'total_listings' => $user->listings()->count(),
            'active_listings' => $user->listings()->where('status', 'active')->count(),
            'pending_listings' => $user->listings()->where('status', 'pending')->count(),
            'total_views' => $user->listings()->sum('view_count'),
        ];

        $recentListings = $user->listings()->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentListings'));
    }

    public function myListings()
    {
        $listings = auth()->user()->listings()->with(['category', 'location'])->latest()->paginate(10);

        return view('dashboard.listings', compact('listings'));
    }

    public function createListing()
    {
        $categories = \App\Models\Category::active()->with('subcategories')->get();
        $listingTypes = \App\Models\ListingType::all();
        $locations = \App\Models\Location::where('type', 'city')->orderBy('name')->get();

        return view('dashboard.listings.create', compact('categories', 'listingTypes', 'locations'));
    }

    public function storeListing(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'listing_type_id' => 'required|exists:listing_types,id',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'required|in:fixed,negotiable,free,on_request',
            'location_id' => 'required|exists:locations,id',
        ]);

        $listing = auth()->user()->listings()->create([
            ...$validated,
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard.listings')->with('success', 'Skelbimas sukurtas ir laukia patvirtinimo!');
    }
}
