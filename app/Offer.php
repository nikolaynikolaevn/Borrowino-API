<?php

namespace App;

use App\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Offer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'location', 'photos', 'price'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return Image::where('type', 'offer_image')->where('resource_id', $this->id)->get();
    }
}
