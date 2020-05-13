<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferRequest extends Model
{
    protected $fillable = ['borrower', 'offer', 'from', 'until', 'description', 'status'];

    public function accept()
    {
        $this->status = 'accepted';
        $this->save();
    }

    public function decline()
    {
        $this->status = 'declined';
        $this->save();
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower');
    }
}
