<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Game;

class PivotRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Register some users to games
        for($i=1; $i<4; $i++)
        {
            User::find($i)->enrollIntoGame();
        }


    }
}
