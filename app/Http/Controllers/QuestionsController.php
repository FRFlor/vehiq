<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Question;
use App\Game;

class QuestionsController extends Controller
{
    function index()
    {
        return view('question.index');
    }

    function show(Question $question)
    {
        return view('question.show', compact('question'));
    }


    function testAnswer(Request $request)
    {
        $questionId = $request->get('id');
        $answerProvided = $request->get('answer');

        $question = Question::findOrFail($questionId);

        return response()->json([
            'isAnswerRight' => $question->isAnswerRight($answerProvided)
        ],Response::HTTP_OK);
    }


}
