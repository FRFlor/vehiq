<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use App\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    function playGame()
    {
        if (Auth::user() === null) {
            return view('welcome');
        }

        return view('game.play', [
            'secondsToGame' => Game::currentGame()->secondsUntilStart
        ]);
    }

    function getSecondsToGame(Request $request)
    {
        $gameId = $request->get('gameId');
        if ($gameId === null) {
            return response()->json([
                'secondsToGame' => Game::currentGame()->secondsUntilStart
            ], response::HTTP_OK);
        }

        return response()->json([
            'secondsToGame' => Game::findOrFail($gameId)->secondsUntilStart
        ], response::HTTP_OK);
    }

    function getCurrentQuestion()
    {
        $currentGame = Game::currentGame();

        // Retrieve the models
        if ($currentGame->isOver) {
            return null;
        }

        $question = $currentGame->currentQuestion;

        // Get only the necessary attributes
        // Obs: questionNumber is the number of the question within the quiz
        //      For example, Question 2 of 12
        //                  (questionNumber == 2)
        return response()->json([
            'id' => $question->id,
            'questionNumber' => $currentGame->currentQuestionNumber,
            'statement' => $question->statement,
            'choices' => $question->shuffledAnswers,
        ], response::HTTP_OK);

    }

    function askNextQuestion()
    {
        // Retrieve the models
        $currentGame = Game::currentGame();
        $isThereAnotherQuestion = $currentGame->gotoNextQuestion();


        if ($isThereAnotherQuestion) {
            $question = $currentGame->currentQuestion;

            // Get only the necessary attributes
            // Obs: questionNumber is the number of the question within the quiz
            //      For example, Question 2 of 12
            //                  (questionNumber == 2)
            return view('game.askQuestion', ['questionData' => [
                'id' => $question->id,
                'questionNumber' => $currentGame->currentQuestionNumber,
                'statement' => $question->statement,
                'choices' => $question->shuffledAnswers,
            ]]);
        }


        return view('game.notPlaying');

    }


    function answerQuestion(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $answerGiven = $request->input('answerGiven');

        // Collect the models
        $currentUser = User::findBySecretToken($userSecret);

        $currentUser->answerQuestion($answerGiven);


        // TODO: Remove this response
        return response()->json([
            'isAnswerRight' => true,
            'currentScore' => 0
        ], Response::HTTP_OK);
    }


    function notifyTimeOut()
    {
        $currentGame = Game::currentGame();

    }

}
