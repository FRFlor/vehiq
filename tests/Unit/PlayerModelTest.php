<?php

namespace Tests\Unit;

use App\Player;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerModelTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testItKeepsScore()
    {
        // Reset the test ground
        Player::truncate();
        $this->assertEquals(Player::topScore(), 0); // Top Score is equal to 0

        $this->seed("PlayersTableSeeder");

        $player = Player::find(1);

        // Put 1 player on the lead
        $player->incrementScore();
        $topPlayers = Player::lead();
        $this->assertEquals(Player::topScore(), 1); // Top Score is equal to 1

    }


    public function testItCalculatesLead()
    {
        // Reset the test ground
        Player::truncate();

        $this->seed("PlayersTableSeeder");

        $player1 = Player::find(1);
        $player2 = Player::find(2);

        // Put 1 player on the lead
        $player1->incrementScore();
        $topPlayers = Player::lead();
        $this->assertEquals($topPlayers->count(), 1); // Only 1 player on the lead
        $this->assertEquals($topPlayers[0]->id, 1); // Player with id == 1

        // Put 2 players on the lead
        $player2->incrementScore();
        $topPlayers = Player::lead();
        $this->assertEquals($topPlayers->count(), 2);
    }
}
