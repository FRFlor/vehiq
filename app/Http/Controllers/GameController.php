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
        // The game can only be played if the user that requested is not yet disqualified
        if (Auth::user()->isDisqualified ||
            Game::currentGame()->isOver)
        {
            return view('game.notPlaying');
        }

        return $this->askNextQuestion();
    }


    function askNextQuestion()
    {
        // Retrieve the models
        $currentGame = Game::currentGame();
        $isThereAnotherQuestion = $currentGame->gotoNextQuestion();


        if ($isThereAnotherQuestion)
        {
            $question = $currentGame->currentQuestion;

            // Get only the necessary attributes
            // Obs: questionNumber is the number of the question within the quiz
            //      For example, Question 2 of 12
            //                  (questionNumber == 2)
            $questionData = [
                'id' => $question->id,
                'questionNumber' => $currentGame->currentQuestionNumber,
                'statement' => $question->statement,
                'choices' => $question->shuffledAnswers,
            ];

            return view('game.askQuestion', compact('questionData'));
        }



        return view('game.notPlaying');

    }


    function answerQuestion(Request $request)
    {
        // Fetch the data from the request
        $userId = $request->input('userId');
        $answerGiven = $request->input('answerGiven');

        // Collect the models
        $currentPlayer = User::find($userId);
        $currentGame = Game::currentGame();

        // Update the answers counters for the target question
        $currentGame->currentQuestion->registerAnswer($answerGiven);

        // See if the answer was correct
        $isAnswerRight = $currentGame->currentQuestion->isAnswerRight($answerGiven);

        if ($isAnswerRight)
        {
            $currentPlayer->incrementScore();
        }
        else
        {
            $currentPlayer->disqualify();
        }

        return response()->json([
            'isAnswerRight' => $isAnswerRight
        ],Response::HTTP_OK);
    }

}
