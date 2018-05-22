<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->cleanDb();
        $this->call(UsersTableSeeder::class);
        $this->call(GamesTableSeeder::class);
        $this->call(QuestionsTableSeeder::class);
        $this->call(PivotRelationsSeeder::class);
    }

    public function cleanDb(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        App\User::truncate();
        App\Game::truncate();
        App\Question::truncate();
        DB::table('game_user')->truncate();
        DB::table('question_user')->truncate();
        DB::table('oauth_clients')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
