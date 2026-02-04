<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'is_active', 'position'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('position');
    }
}
