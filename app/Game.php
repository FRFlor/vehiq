<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Student note::
// To allow the quick creation of this property header I used:
// https://github.com/barryvdh/laravel-ide-helper
// Instructions: php artisan ide-helper:models -R


/**
 * App\Game
 *
 * @property int $id
 * @property string $startTime
 * @property int $secondsToAnswerQuestion
 * @property int $secondsToReadQuestionStats
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @property-read mixed $areAllPlayersDisqualified
 * @property-read mixed $currentQuestion
 * @property-read mixed $currentQuestionNumber
 * @property-read mixed $isCurrentQuestionBeingAsked
 * @property-read mixed $isCurrentQuestionDisplayingStats
 * @property-read mixed $isOver
 * @property-read mixed $minimumSecondsToShowStatsOfAllAnswers
 * @property-read mixed $numberOfQuestions
 * @property-read mixed $secondsRemainingToAnswerQuestion
 * @property-read mixed $secondsRemainingToReadQuestionStats
 * @property-read mixed $secondsSinceStarted
 * @property-read mixed $secondsUntilStart
 * @property-read mixed $totalSecondsPerQuestion
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereSecondsToAnswerQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereSecondsToReadQuestionStats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Game extends Model
{
    protected $fillable = ['startTime', 'secondsPerQuestion', 'currentQuestionNumber'];
    const NO_QUESTION_NUMBER = 0;

    function questions()
    {
        return $this->hasMany(Question::class);
    }

    function users()
    {
        return $this->belongsToMany(User::class);
    }

    static function createNewGame($secondsUntilStart = 120,
                                  $secondsToAnswerQuestion = 10, $secondsToReadQuestionStats = 10)
    {
        $newGame = new Game;
        $newGame->startTime = Carbon::now()->addSeconds($secondsUntilStart);
        $newGame->secondsToAnswerQuestion = $secondsToAnswerQuestion;
        $newGame->secondsToReadQuestionStats = $secondsToReadQuestionStats;

        $newGame->save();

        //TODO: Receive Array of Questions
        for ($i = 1; $i <= 12; $i++) {
            Question::addNewQuestionToGame(
                $newGame->id,
                "Is this a placeholder? $i",
                "Right answer",
                "Wrong Answer 1",
                "Wrong Answer 2");
        }

    }


    function getCurrentQuestionNumberAttribute()
    {
        // 1) Calculate how many seconds have passed
        if ($this->secondsSinceStarted < 0) {   // Game hasn't started yet
            return static::NO_QUESTION_NUMBER;
        }

        if ($this->secondsSinceStarted > $this->totalSecondsPerQuestion * $this->questions->count()) {   // Game has ended already
            return static::NO_QUESTION_NUMBER;
        }

        return floor($this->secondsSinceStarted / $this->totalSecondsPerQuestion) + 1;
    }

    static function upcomingGame()
    {
        $latestGame = static::currentGame();

        if ($latestGame->secondsUntilStart < 0) {
            return null;
        }

        return $latestGame;
    }

    static function currentGame($userId = null)
    {
        if ($userId == null) {
            return static::orderBy('id', 'DESC')->first();
        }

        return User::find($userId)->games()->orderBy('id', 'DESC')->first();;

    }

    function getTotalSecondsPerQuestionAttribute()
    {
        return $this->secondsToAnswerQuestion + $this->secondsToReadQuestionStats;
    }

    function getSecondsRemainingToAnswerQuestionAttribute()
    {
        if ($this->currentQuestionNumber === static::NO_QUESTION_NUMBER) {
            return 0;
        }


        return $this->currentQuestionNumber * $this->totalSecondsPerQuestion
            - $this->secondsToReadQuestionStats - $this->secondsSinceStarted;
    }

    function getSecondsRemainingToReadQuestionStatsAttribute()
    {
        if ($this->currentQuestionNumber === static::NO_QUESTION_NUMBER) {
            return 0;
        }

        if ($this->secondsRemainingToAnswerQuestion > 0) {
            return 0;
        }


        return $this->currentQuestionNumber * $this->totalSecondsPerQuestion - $this->secondsSinceStarted;

    }


    // A question is either being asked or displaying answers stats
    function getIsCurrentQuestionBeingAskedAttribute()
    {
        return ($this->secondsRemainingToAnswerQuestion > 0);
    }

    function getIsCurrentQuestionDisplayingStatsAttribute()
    {
        return ($this->secondsToReadQuestionStats > 0);
    }

    function getCurrentQuestionAttribute()
    {
        if ($this->currentQuestionNumber === static::NO_QUESTION_NUMBER) {
            return null;
        }

        return $this->questions()
            ->orderBy('id', 'ASC')
            ->skip($this->currentQuestionNumber - 1)
            ->first();
    }

    function getIsOverAttribute()
    {
        // There has been enough time to show all questions... end the game
        if ($this->secondsSinceStarted > 0 && $this->currentQuestionNumber === static::NO_QUESTION_NUMBER) {
            return true;
        }

        // If all players are disqualified and there has been enough time to show the statistics of the
        // question that disqualified the very last player... end the game
        if ($this->areAllPlayersDisqualified &&
            $this->secondsSinceStarted >= $this->minimumSecondsToShowStatsOfAllAnswers) {
            return true;
        }

        return false;
    }

    function getAreAllPlayersDisqualifiedAttribute()
    {
        foreach ($this->users as $player) {
            if (!$player->isDisqualified) {   // At least one player is still playing
                return false;
            }
        }

        return true;
    }

    function getNumberOfQuestionsAttribute()
    {
        return $this->questions()->count();
    }


    public function getSecondsUntilStartAttribute()
    {

        return Carbon::now()->diffInSeconds($this->startTime, false);
    }

    public function getSecondsSinceStartedAttribute()
    {
        return (-1) * $this->secondsUntilStart;
    }


    public function getMinimumSecondsToShowStatsOfAllAnswersAttribute()
    {
        $lastQuestionNumber = 0;
        foreach ($this->questions()->orderBy('id', 'ASC')->get() as $question) {
            if ($question->users()->count() == 0) {
                break;
            }
            $lastQuestionNumber++;
        }

        if ($lastQuestionNumber === 0) {
            return 0;
        }

        return $this->totalSecondsPerQuestion * $lastQuestionNumber;
    }


}
