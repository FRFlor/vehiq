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

        return response()->json(['hasJoinedGame' => $currentUser->enrollIntoGame()], Response::HTTP_OK);
    }

    function answerQuestion(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $currentUser = User::findBySecretToken($userSecret);

        $answerGiven = $request->input('answerGiven');
        $currentUser->answerQuestion($answerGiven);

        return response()->json(['answerStored' => true], Response::HTTP_OK);
    }


    function getStatus(Request $request)
    {
        // Fetch the data from the request
        $userSecret = $request->input('userSecretToken');
        $currentUser = User::findBySecretToken($userSecret);

        if(!$currentUser->isCurrentInGame){
            $secondsForNextGame = 0;
            if (Game::upcomingGame())
            {
                $secondsForNextGame = Game::upcomingGame()->secondsUntilStart;
            }
            return response()->json([
                'status' => 'Not in Game',
                'secondsRemaining' => $secondsForNextGame], Response::HTTP_OK);
        }

        $currentGame = Game::currentGame($currentUser->id);

        if($currentGame->secondsUntilStart > 0){
            return response()->json([
                'status' => 'Waiting for Game',
                'secondsRemaining' => $currentGame->secondsUntilStart], Response::HTTP_OK);
        }

        $currentQuestion = $currentGame->currentQuestion;

        // At this point it is known that the game has already started
        return response()->json([
            'status' => 'Asking Question',
            'secondsRemaining' => $currentGame->secondsRemainingInQuestion,
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



}
