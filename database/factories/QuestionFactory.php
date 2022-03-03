<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Question::class, function (Faker $faker) {
    return [
        'title' => $faker->text(40),
    ];
});
