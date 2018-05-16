<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfUsers = 5;

        foreach(range(1,$numberOfUsers) as $i)
        {
            factory(App\User::class)->create();
        }
    }
}
