<?php

namespace App;

use Illuminate\Database\Eloquent\Model;




/**
 * App\Question
 *
 * @property int $id
 * @property int $gameId
 * @property string $statement
 * @property string $rightAnswer
 * @property string $wrongAnswer1
 * @property string $wrongAnswer2
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @property-read \App\Game $games
 * @property-read mixed $answerSelectionCount
 * @property-read mixed $shuffledAnswers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereRightAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereWrongAnswer1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Question whereWrongAnswer2($value)
 * @mixin \Eloquent
 */
class Question extends Model
{
    function users()
    {
        return $this->belongsToMany(User::class)->withPivot('answerGiven');
    }


    function games()
    {
        return $this->belongsTo(Game::class);
    }

    // Checks if an answer given matches with this question's right answer
    function isAnswerRight($answer)
    {
        return trim($answer) === trim($this->rightAnswer);
    }

    static function addNewQuestionToGame($gameId, $statement, $rightAnswer, $wrongAnswer1, $wrongAnswer2)
    {
        $question = new Question;
        $question->statement = $statement;
        $question->rightAnswer = $rightAnswer;
        $question->wrongAnswer1 = $wrongAnswer1;
        $question->wrongAnswer2 = $wrongAnswer2;

        Game::find($gameId)->questions()->save($question);
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
