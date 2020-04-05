<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Daydevelops\Vote\Models\Comment;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory('Daydevelops\Vote\Models\User')->create()->id;
        },
    ];
});
