<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Daydevelops\Vote\Tests\Models\Comment;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory('Daydevelops\Vote\Tests\Models\User')->create()->id;
        },
    ];
});
