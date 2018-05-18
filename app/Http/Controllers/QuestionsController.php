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
        // TODO: Do not send model
        return view('question.show', ['question' => $question]);
    }


}
