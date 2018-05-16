<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $currentGame = Game::currentGame();
        $currentGame->goToNextQuestion();
        $question = $currentGame->currentQuestion;

        $questionNumber = $currentGame->currentQuestionNumber;
        return view('question.ask', compact('question','questionNumber'));
    }


    function answerQuestion(Request $request)
    {

        $userId = $request->input('userId',1);
        $answerGiven = $request->input('answerGiven');

        $currentGame = Game::currentGame();

        $currentGame->currentQuestion->registerAnswer($answerGiven);

        return response()->json([
            'isAnswerRight' => $currentGame->currentQuestion->isAnswerRight($answerGiven)
        ],Response::HTTP_OK);
    }

}
