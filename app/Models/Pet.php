<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'name',
        'breed',
        'birth_year',
        'qr_code',
        'image',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array<int, string>
     */
    protected $visible = [
        'id',
        'user_id',
        'qr_code_url',
        'name',
        'breed',
        'birth_year',
        'qr_code',
        'image',
        'token',
    ];

    /**
     * The user that owns the pet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
