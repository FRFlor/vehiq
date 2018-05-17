<?php

namespace Tests\Unit;

use App\Game;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameModelTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $currentGame = Game::currentGame();
        $currentGame->currentQuestionNumber = 1;
        $currentQuestion = $currentGame->currentQuestion;
        $this->assertEquals($currentQuestion->id, 1);
        $currentGame->gotoNextQuestion();
        $currentQuestion = $currentGame->currentQuestion;
        $this->assertEquals($currentQuestion->id, 2);

    }
}
