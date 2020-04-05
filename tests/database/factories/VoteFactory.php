<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Daydevelops\Vote\Models\Vote;
use Faker\Generator as Faker;

$factory->define(Vote::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory("Daydevelops\Vote\Models\User")->create()->id;
        },
        'voted_id' => function () {
            return factory("Daydevelops\Vote\Models\Comment")->create()->id;
        },
        'voted_type' => "Daydevelops\Vote\Models\Comment",
        'value' => [-1, 1][rand(0, 1)]
    ];
});
