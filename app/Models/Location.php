<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function scopeCities($query)
    {
        return $query->where('type', 'city');
    }

    public function scopeCountries($query)
    {
        return $query->where('type', 'country');
    }
}
