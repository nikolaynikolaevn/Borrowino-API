<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferRequest extends Model
{
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
}
