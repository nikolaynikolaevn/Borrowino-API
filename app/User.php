<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','isAdmin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function images()
    {
        return Image::where('type', 'profile_image')->where('resource_id', $this->id)->get();
    }

    public function offers()
    {
        return Offer::where('owner', $this->id)->paginate(15);
    }

    public function received_requests()
    {
        return $this->hasManyThrough(
            OfferRequest::class,
            Offer::class,
            'owner',
            'offer',
            'id',
            'id'
        );
    }
}
