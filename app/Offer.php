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
        'title', 'description', 'location', 'photos', 'price', 'expires'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return Image::where('type', 'offer_image')->where('resource_id', $this->id)->get();
    }

    public function requests()
    {
        return $this->hasMany(OfferRequest::class, 'offer');
    }
}
