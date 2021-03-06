<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Offer;
use App\User;
use Faker\Generator as Faker;

$factory->define(Offer::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(25),
        'description' => $faker->realText(200),
        'location' => $faker->city,
        'price' => $faker->randomFloat(2, 1, 1000),
        'expires' => $faker->dateTimeBetween('now', '+10 days')->format('Y-m-d H:i:s'),
        'owner'=> function() {
            return factory(User::class)->create()->id;
        },
    ];
});
