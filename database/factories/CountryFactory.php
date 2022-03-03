<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Models\Country;
use Faker\Generator as Faker;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'Iso' => $faker->countryCode,
        'title' => $faker->country,
        'Iso3' => $faker->countryISOAlpha3,
    ];
});
