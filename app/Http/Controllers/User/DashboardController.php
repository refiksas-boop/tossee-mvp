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
        $locations = \App\Models\Location::all();
        $subcategories = \App\Models\Subcategory::where('is_active', true)->get();

        // Get all attributes grouped by category
        $attributesByCategory = \App\Models\Attribute::with('options')
            ->whereNotNull('category_id')
            ->orderBy('position')
            ->get()
            ->groupBy('category_id')
            ->map(function ($attributes) {
                return $attributes->values();
            });

        return view('dashboard.listings.create', compact(
            'categories',
            'listingTypes',
            'locations',
            'subcategories',
            'attributesByCategory'
        ));
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
            'attributes' => 'nullable|array',
            'images.*' => 'nullable|image|max:5120', // max 5MB
        ]);

        // Create listing
        $listing = auth()->user()->listings()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'listing_type_id' => $validated['listing_type_id'],
            'price' => $validated['price'] ?? null,
            'price_type' => $validated['price_type'],
            'location_id' => $validated['location_id'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'status' => 'pending',
        ]);

        // Save attributes
        if (!empty($validated['attributes'])) {
            foreach ($validated['attributes'] as $attributeId => $value) {
                if (is_array($value)) {
                    // Multiselect - save each option
                    foreach ($value as $optionId) {
                        \App\Models\ListingAttributeValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attributeId,
                            'attribute_option_id' => $optionId,
                        ]);
                    }
                } elseif (!empty($value)) {
                    // Get attribute type
                    $attribute = \App\Models\Attribute::find($attributeId);

                    if ($attribute && in_array($attribute->type, ['select', 'radio'])) {
                        // Save option ID
                        \App\Models\ListingAttributeValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attributeId,
                            'attribute_option_id' => $value,
                        ]);
                    } else {
                        // Save raw value
                        \App\Models\ListingAttributeValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attributeId,
                            'value_text' => $value,
                        ]);
                    }
                }
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $position = 1;
            foreach ($request->file('images') as $image) {
                if ($position > 10) break; // Max 10 images

                $path = $image->store('listings', 'public');

                \App\Models\MediaFile::create([
                    'listing_id' => $listing->id,
                    'file_type' => 'image',
                    'path' => $path,
                    'position' => $position,
                    'is_main' => $position === 1,
                ]);

                $position++;
            }
        }

        return redirect()->route('dashboard.listings')->with('success', 'Skelbimas sukurtas ir laukia patvirtinimo!');
    }
}
