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
        return view('game.play');
    }

    function joinGame(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $currentUser = User::findBySecretToken($userSecret);

        if(!$currentUser->joinGame()){
            return response('', Response::HTTP_BAD_REQUEST);
        }

        return response('', Response::HTTP_OK);
    }

    function answerQuestion(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $currentUser = User::findBySecretToken($userSecret);

        $answerGiven = $request->input('answerGiven');

        if ($currentUser->isDisqualified ||
        !$currentUser->isCurrentlyInGame ||
        $currentUser->hasAnsweredCurrentQuestion){
            return response('',Response::HTTP_BAD_REQUEST);
        }

        $currentUser->answerQuestion($answerGiven);

        return response('',Response::HTTP_OK);
    }


    function getStatus(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $currentUser = User::findBySecretToken($userSecret);

        // Case 1: User is not in a game at all
        if(!$currentUser->isCurrentlyInGame){
            // Is there an upcoming game?
            $secondsForNextGame = Game::upcomingGame()? Game::upcomingGame()->secondsUntilStart : 0;

            return response()->json([
                'status' => 'Not in Game',
                'secondsRemaining' => $secondsForNextGame], Response::HTTP_OK);
        }

        $currentGame = Game::currentGame($currentUser->id);

        // Case 2: User is in a game, but it hasn't started yet
        if($currentGame->secondsUntilStart > 0){
            return response()->json([
                'status' => 'Waiting for Game',
                'secondsRemaining' => $currentGame->secondsUntilStart], Response::HTTP_OK);
        }

        $currentQuestion = $currentGame->currentQuestion;

        // Case 3: The user is in a game and is in the process of answering a question
        if ($currentGame->isCurrentQuestionBeingAsked)
        {
            return response()->json([
                'status' => 'Asking Question',
                'secondsRemaining' => $currentGame->secondsRemainingToAnswerQuestion,
                'player' => [
                    'isDisqualified' =>   $currentUser->isDisqualified,
                    'score' => $currentUser->score,
                ],
                'currentQuestion' => [
                    'questionNumber' => $currentGame->currentQuestionNumber,
                    'statement' => $currentQuestion->statement,
                    'choices' => $currentQuestion->shuffledAnswers,
                ],
            ]);
        }

        // Case 4: The user is seeing question statistics
        return response()->json([
            'status' => 'Viewing Answer Poll',
            'secondsRemaining' => $currentGame->secondsToReadQuestionStats,
            'player' => [
                'isDisqualified' =>   $currentUser->isDisqualified,
                'score' => $currentUser->score,
            ],
            'currentQuestion' => [
                'questionNumber' => $currentGame->currentQuestionNumber,
                'statistics' => $currentQuestion->answerSelectionCount,
            ],
        ]);
    }



}
