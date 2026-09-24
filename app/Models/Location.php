<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'latitude', 'longitude'];

    public function chargers()
    {
        return $this->hasMany(Charger::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}