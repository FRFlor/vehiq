<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    function questions()
    {
        return $this->hasMany('App\Question');
    }

    static function currentGame()
    {
        return static::orderBy('id','DESC')->first();
    }

    function getCurrentQuestionAttribute()
    {
        return $this->questions()
            ->orderBy('id','ASC')
            ->skip($this->currentQuestionNumber-1)
            ->first();
    }

    function getAllQuestionsAttribute()
    {
        return $this->questions()->orderBy('id','ASC')->get();
    }

    function getNumberOfQuestionsAttribute()
    {
        return $this->questions()->count();
    }

    //
    function createGame($startTime, $secondsPerQuestion = 10)
    {
        $newGame = new Game();

        $newGame->startTime = $startTime;
        $newGame->secondsPerQuestion = $secondsPerQuestion;

        return $newGame->save();
    }



    public function gotoNextQuestion()
    {
        $this->currentQuestionNumber++;

        if ($this->currentQuestionNumber > $this->numberOfQuestions)
        {
            $this->currentQuestionNumber = 1;
        }

        return $this->save();
    }
}
