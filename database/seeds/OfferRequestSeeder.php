<?php

use App\OfferRequest;
use Illuminate\Database\Seeder;

class OfferRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(OfferRequest::class, 50)->create();
    }
}
