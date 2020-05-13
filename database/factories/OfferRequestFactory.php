<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Offer;
use App\OfferRequest;
use App\User;
use Faker\Generator as Faker;

$factory->define(OfferRequest::class, function (Faker $faker) {
    return [
        'borrower'=> User::all()->random()->id,
        'offer'=> Offer::all()->random()->id,
        'from' => $faker->dateTimeBetween('-2 months', '-2 months'),
        'until' => $faker->dateTimeBetween('-2 months'),
        'description' => $faker->text(100),
        'status' => $faker->randomElement(['accepted', 'declined']),
    ];
});
