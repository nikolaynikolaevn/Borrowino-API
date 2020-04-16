<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'location', 'photos'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
