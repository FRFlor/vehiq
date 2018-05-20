<?php

namespace Tests\Feature;

use App\Game;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class GameApiTest extends TestCase
{

    // Will the API properly update the counter column (rightAnswerCounter, wrongAnswer1Counter, ...) at the correct question row?
    public function testItUpdatesTheChoicesCounters()
    {

    }


    public function testItGetsSecretProperly()
    {
        $this->actingAs(User::find(2));

        $response = $this->getJson('/oauth/clients');
        dd($response->dump());

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
