<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Question;

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

    function askRandom()
    {
        $selectedQuestionId = rand(1,Question::count());
        $question = Question::find($selectedQuestionId);
        return view('question.ask', compact('question'));
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
