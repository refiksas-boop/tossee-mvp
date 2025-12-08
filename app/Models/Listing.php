<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
        'listing_type_id',
        'title',
        'slug',
        'description',
        'price',
        'price_type',
        'currency',
        'location_id',
        'address',
        'lat',
        'lng',
        'status',
        'published_at',
        'expires_at',
        'is_featured',
        'is_urgent',
        'view_count',
        'contact_phone',
        'contact_email',
        'contact_via_messages',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'contact_via_messages' => 'boolean',
        'price' => 'decimal:2',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function listingType()
    {
        return $this->belongsTo(ListingType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class)->orderBy('position');
    }

    public function mainImage()
    {
        return $this->hasOne(MediaFile::class)->where('is_main', true);
    }

    public function attributeValues()
    {
        return $this->hasMany(ListingAttributeValue::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Helper methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
