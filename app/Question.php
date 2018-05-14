<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer $id
 * @property string $statement
 * @property string $rightAnswer
 * @property string $wrongAnswer1
 * @property string $wrongAnswer2
 * @property string $wrongAnswer3
 * @property string $wrongAnswer4
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Question extends Model
{

    // Checks if an answer given matches with this question's right answer
    function isAnswerRight($answer)
    {
        return ($answer === $this->rightAnswer);
    }


    function getShuffledJson()
    {
        $possibleAnswers = [
            $this->rightAnswer,
            $this->wrongAnswer1,
            $this->wrongAnswer2,
            $this->wrongAnswer3,
            $this->wrongAnswer4
        ];

        shuffle($possibleAnswers);

        return response()->json([
            'id' => $this->id,
            'statement' => $this->statement,
            'choiceA' => $possibleAnswers[0],
            'choiceB' => $possibleAnswers[1],
            'choiceC' => $possibleAnswers[2],
            'choiceD' => $possibleAnswers[3],
            'choiceE' => $possibleAnswers[4]
        ])->getData();

    }

}
