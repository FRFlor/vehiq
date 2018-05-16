<?php

namespace Tests\Feature;

use App\Game;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testApiAnswerQuestion()
    {
        $currentGame = Game::currentGame();
        $userId = 1;
        $answerGiven =  $currentGame->currentQuestion->wrongAnswer1;


        $response = $this->postJson('/api/game/answerQuestion',[
            'userId' => $userId,
            'answerGiven' => $answerGiven
        ]);


        $response->assertJsonFragment(['isAnswerRight'=>false]);


        $answerGiven =  $currentGame->currentQuestion->rightAnswer;

        $response = $this->postJson('/api/game/answerQuestion',[
            'userId' => $userId,
            'answerGiven' => $answerGiven
        ]);

        $response->assertJsonFragment(['isAnswerRight'=>true]);
    }
}
