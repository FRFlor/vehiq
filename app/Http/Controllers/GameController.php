<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Player;
use App\Game;
use Carbon\Carbon;

class GameController extends Controller
{
       static $currentQuestion = 0;




       function resetPlayingField()
       {
           Player::truncate();
           Game::truncate();
       }


       function startGame($delayInSeconds, $secondsPerQuestion = 10)
       {
            $startTime = Carbon::now()->addSeconds($delayInSeconds);

       }


       function endGame()
       {
           Player::truncate();
       }


    function askRandom()
    {
        $selectedQuestionId = rand(1,Question::count());
        $question = Question::find($selectedQuestionId);
        return view('question.ask', compact('question'));
    }

    function askNextQuestion()
    {
        $currentGame = Game::current();
        $question = $currentGame->getNextQuestion();
        $questionNumber = $currentGame->currentQuestion;
        return view('question.ask', compact('question','questionNumber'));
    }

}
