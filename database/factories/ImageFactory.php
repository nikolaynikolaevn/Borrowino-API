<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Image;
use Faker\Generator as Faker;

$factory->define(Image::class, function (Faker $faker) {
    return [
        'resource_id' => $faker->numberBetween(1, 10),
        'type' => 'offer_image',
        'path_to_image' => 'images/' . $faker->text(10) .'jpg',
    ];
});
