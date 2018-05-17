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

    function getIsOverAttribute()
    {
        return ($this->currentQuestionNumber > $this->numberOfQuestions);

        // TODO: Game is also over when all players are disqualified!
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
        $isThereAnotherQuestion = false;

        $this->currentQuestionNumber++;

        if (!$this->isOver)
        {
            $isThereAnotherQuestion = true;
        }

        return ($this->save() && $isThereAnotherQuestion);
    }
}
