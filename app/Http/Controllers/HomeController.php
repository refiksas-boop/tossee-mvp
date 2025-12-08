<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::active()->get();
        $featuredListings = \App\Models\Listing::active()
            ->featured()
            ->with(['category', 'location', 'mainImage'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $latestListings = \App\Models\Listing::active()
            ->with(['category', 'location', 'mainImage'])
            ->latest('published_at')
            ->take(12)
            ->get();

        return view('home', compact('categories', 'featuredListings', 'latestListings'));
    }
}
