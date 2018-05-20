<?php

use Faker\Generator as Faker;
use Carbon\Carbon;


$factory->define(App\Game::class, function (Faker $faker) {
    $fakeNow = new Carbon('11:11:20');
    $fakeNow = Carbon::now();

    return [
        'startTime' => $fakeNow->addSeconds(60)
    ];
});
