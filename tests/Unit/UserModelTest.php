<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    // Will the User Model properly keep track of the users score?
    public function testItKeepsScore()
    {
        // Reset the test ground
        $this->seed();
        $this->assertEquals(User::topScore(), 0); // Top Score is equal to 0

        $this->seed("UsersTableSeeder");

        $player = User::find(1);

        // Put 1 player on the lead
        $player->incrementScore();
        $topPlayers = User::lead();
        $this->assertEquals(User::topScore(), 1); // Top Score is equal to 1

    }


    // Will the User Model properly obtain the list of users that have the maximum score?
    public function testItCalculatesLead()
    {
        // Reset the test ground
        $this->seed();

        $player1 = User::find(1);
        $player2 = User::find(2);

        // Put 1 player on the lead
        $player1->incrementScore();
        $topUsers = User::lead();
        $this->assertEquals($topUsers->count(), 1); // Only 1 player on the lead
        $this->assertEquals($topUsers[0]->id, 1); // User with id == 1

        // Put 2 players on the lead
        $player2->incrementScore();
        $topUsers = User::lead();
        $this->assertEquals($topUsers->count(), 2);
    }
}
