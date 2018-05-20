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
    function users()
    {
        return $this->belongsToMany(User::class)->withPivot('answerGiven');
    }


    function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Checks if an answer given matches with this question's right answer
    function isAnswerRight($answer)
    {
        return trim($answer) === trim($this->rightAnswer);
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

    function getAnswerSelectionCountAttribute()
    {
        $rightAnswerCount = 0;
        $wrongAnswer1Count = 0;
        $wrongAnswer2Count = 0;

        foreach($this->users as $userResponse)
        {
            switch(trim($userResponse->pivot->answerGiven))
            {
                case trim($this->rightAnswer):
                    $rightAnswerCount++;
                    break;
                case trim($this->wrongAnswer1):
                    $wrongAnswer1Count++;
                    break;
                case trim($this->wrongAnswer2):
                    $wrongAnswer2Count++;
                    break;
            }
        }

        return [
            ['answerText' => $this->rightAnswer,
                'count' => $rightAnswerCount],
            ['answerText' => $this->wrongAnswer1,
                'count' => $wrongAnswer1Count],
            ['answerText' => $this->wrongAnswer2,
                'count' => $wrongAnswer2Count],
        ];
    }

}
