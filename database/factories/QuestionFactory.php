<?php

use Faker\Generator as Faker;

$factory->define(App\Question::class, function (Faker $faker) {
    return [
        'statement' => ucfirst($faker->words(8,true).'?'),
        'game_Id' => App\Game::count(),
        'rightAnswer' => ucfirst($faker->words(3,true).'(Right One)'),
        'wrongAnswer1' => ucfirst($faker->words(3,true)),
        'wrongAnswer2' => ucfirst($faker->words(3,true))
    ];
});
