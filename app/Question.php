<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer $id
 * @property string $statement
 * @property string $rightAnswer
 * @property string $wrongAnswer1
 * @property string $wrongAnswer2
 * @property string $rightAnswerCount
 * @property string $wrongAnswer1Count
 * @property string $wrongAnswer2Count
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Question extends Model
{

    function game()
    {
        return $this->belongsTo('App\Game');
    }

    // Checks if an answer given matches with this question's right answer
    function isAnswerRight($answer)
    {
        return trim($answer) === trim($this->rightAnswer);
    }


    // There has to be a better way...
    function registerAnswer($answer)
    {
        if(trim($answer) === trim($this->rightAnswer))
        {
            $this->rightAnswerCount++;
        }
        else if(trim($answer) === trim($this->wrongAnswer1))
        {
            $this->wrongAnswer1Count++;
        }
        else if(trim($answer) === trim($this->wrongAnswer2))
        {
            $this->wrongAnswer2Count++;
        }


        return $this->save();
    }



    function getShuffledAnswersAttribute()
    {
        $possibleAnswers = [
            $this->rightAnswer,
            $this->wrongAnswer1,
            $this->wrongAnswer2
        ];

        shuffle($possibleAnswers);

        return response()->json([
            'choiceA' => $possibleAnswers[0],
            'choiceB' => $possibleAnswers[1],
            'choiceC' => $possibleAnswers[2],
        ])->getData();

    }

}
