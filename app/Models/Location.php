<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'longitude',
        'latitude'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
