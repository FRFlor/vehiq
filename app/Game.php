<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    function questions()
    {
        return $this->hasMany('App\Question');
    }

    static function current()
    {
        $gameId = static::orderBy('id','DESC')->take(1)->get()[0]->id;

        return static::find($gameId);
    }
    //
    function createGame($startTime, $secondsPerQuestion = 10)
    {
        $newGame = new Game();

        $newGame->startTime = $startTime;
        $newGame->secondsPerQuestion = $secondsPerQuestion;

        return $newGame->save();
    }

    function getAllQuestionsAttribute()
    {
        return $this->questions()->orderBy('id','ASC')->get();
    }

    public function getNextQuestion()
    {
        $this->currentQuestion++;
        $this->save();

        $allQuestions = $this->allQuestions;

        return $allQuestions[$this->currentQuestion - 1];
    }
}
