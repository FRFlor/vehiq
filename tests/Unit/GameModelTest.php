<?php

namespace Tests\Unit;

use Carbon\Carbon;
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


    public function testItReturnsSecondsToNextGame(){
        $this->seed();

        // Comment Fill is update() without the save() step

        // Testing API acts accordingly when receiving an specific gameId
        Game::find(1)->update(['startTime'=> Carbon::now()->addSeconds(120)]);

        $response = $this->getJson('/api/game/secondsToGame?gameId=1');
        $response->assertJsonFragment(['secondsToGame' => 120]);


        // Testing Api knows how to fetch the latest created game if no gameId is provided
        Game::create([
            'startTime' => Carbon::now()->addSeconds(52)
        ]);

        $response = $this->getJson('/api/game/secondsToGame');
        $response->assertJsonFragment(['secondsToGame' => 52]);





        // Testing Api handles when the gameId is provided but empty
        Game::create([
            'startTime' => Carbon::now()->addSeconds(78)
        ]);

        $response = $this->getJson('/api/game/secondsToGame?gameId=');
        $response->assertJsonFragment(['secondsToGame' => 78]);



    }

}
