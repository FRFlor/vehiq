<?php

namespace Tests\Unit;

use App\User;
use App\Game;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameModelTest extends TestCase
{
    // Will the Game Model properly go for the next question it contains every time gotoNextQuestion() is called?
    public function testItFetchesNextQuestion()
    {
        $currentGame = Game::currentGame();
        $currentGame->currentQuestionNumber = 1;

        $currentQuestion = $currentGame->currentQuestion;
        $this->assertEquals($currentQuestion->id, 1);

        $currentGame->gotoNextQuestion();
        $currentQuestion = $currentGame->currentQuestion;
        $this->assertEquals($currentQuestion->id, 2);

    }



    public function testItAllowsAnsweringQuestions()
    {
        $this->seed();
        $user = User::find(1);
        $this->actingAs($user);

        $currentGame = Game::currentGame();
        $currentGame->currentQuestionNumber = 1;
        $answerGiven =  $currentGame->currentQuestion->wrongAnswer1;

        $response = $this->postJson('/api/game/answerQuestion',[
            'answerGiven' => $answerGiven
        ]);

        $response->assertJsonFragment(['isAnswerRight'=>false]);

        $answerGiven =  $currentGame->currentQuestion->rightAnswer;

        $response = $this->postJson('/api/game/answerQuestion',[
            'answerGiven' => $answerGiven
        ]);

        $response->assertJsonFragment(['isAnswerRight'=>true]);
    }

}
