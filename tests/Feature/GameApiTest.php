<?php

namespace Tests\Feature;

use App\Game;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameApiTest extends TestCase
{
    // Will the API properly inform if the answer given is Right or Wrong?
    public function testItRecognizesRightAnswers()
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


    // Will the API properly update the counter column (rightAnswerCounter, wrongAnswer1Counter, ...) at the correct question row?
    public function testItUpdatesTheChoicesCounters()
    {

    }
}
