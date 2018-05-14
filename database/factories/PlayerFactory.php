<?php

use Faker\Generator as Faker;

$factory->define(App\Player::class, function (Faker $faker) {
    return [
        'nickname' => $faker->lexify("????????"),
    ];
});
