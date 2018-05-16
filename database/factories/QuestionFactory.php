<?php

use Faker\Generator as Faker;

$factory->define(App\Question::class, function (Faker $faker) {
    return [
        'statement' => $faker->paragraph,
        'game_Id' => App\Game::count(),
        'rightAnswer' => $faker->sentence.'(Right One)',
        'wrongAnswer1' => $faker->sentence,
        'wrongAnswer2' => $faker->sentence
    ];
});
