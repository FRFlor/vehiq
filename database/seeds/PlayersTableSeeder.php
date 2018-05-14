<?php

use Illuminate\Database\Seeder;

class PlayersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfPlayers = 5;

        foreach(range(1,$numberOfPlayers) as $i)
        {
            factory(App\Player::class)->create();
        }
    }
}
