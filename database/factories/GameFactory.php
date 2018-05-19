<?php

use Faker\Generator as Faker;
use Carbon\Carbon;


$factory->define(App\Game::class, function (Faker $faker) {
    return [
        'startTime' => Carbon::now()->addSeconds(30)
    ];
});
