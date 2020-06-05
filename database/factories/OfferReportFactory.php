<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Offer;
use App\OfferReport;
use App\User;
use Faker\Generator as Faker;

$factory->define(OfferReport::class, function (Faker $faker) {
    return [
        'offer_id' => function () {
            return factory(Offer::class)->create()->id;
        },
        'description' => $faker->realText(30),
        'reporter_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
