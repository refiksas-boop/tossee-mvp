<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Listing::active()->with(['category', 'location', 'mainImage']);

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $category = \App\Models\Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $listings = $query->latest('published_at')->paginate(20);

        return view('listings.index', compact('listings'));
    }

    public function byCategory($categorySlug)
    {
        $category = \App\Models\Category::where('slug', $categorySlug)->with('subcategories')->firstOrFail();

        $listings = \App\Models\Listing::active()
            ->where('category_id', $category->id)
            ->with(['location', 'mainImage'])
            ->latest('published_at')
            ->paginate(20);

        return view('listings.category', compact('category', 'listings'));
    }

    public function bySubcategory($categorySlug, $subcategorySlug)
    {
        $category = \App\Models\Category::where('slug', $categorySlug)->firstOrFail();
        $subcategory = \App\Models\Subcategory::where('slug', $subcategorySlug)
            ->where('category_id', $category->id)
            ->firstOrFail();

        $listings = \App\Models\Listing::active()
            ->where('subcategory_id', $subcategory->id)
            ->with(['location', 'mainImage'])
            ->latest('published_at')
            ->paginate(20);

        return view('listings.subcategory', compact('category', 'subcategory', 'listings'));
    }

    public function show($id, $slug)
    {
        $listing = \App\Models\Listing::with([
            'user.profile',
            'category',
            'subcategory',
            'location',
            'mediaFiles',
            'attributeValues.attribute',
            'attributeValues.option'
        ])->findOrFail($id);

        $listing->incrementViewCount();

        $similarListings = \App\Models\Listing::active()
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'similarListings'));
    }
}
