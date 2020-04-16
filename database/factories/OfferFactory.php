<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Offer;
use Faker\Generator as Faker;

$factory->define(Offer::class, function (Faker $faker) {
    return [
        'title' => $faker->text(25),
        'description' => $faker->text,
        'location' => $faker->city,
        'price' => $faker->randomFloat(2, 1, 1000),
        'owner'=> function() {
            return factory(App\User::class)->create()->id;
        },
    ];
});
