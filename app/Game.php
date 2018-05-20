<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Student note::
// To allow the quick creation of this property header I used:
// https://github.com/barryvdh/laravel-ide-helper
// Instructions: php artisan ide-helper:models Post --dir="App"


/**
 * App\Game
 *
 * @property int $id
 * @property string $startTime
 * @property int $secondsPerQuestion
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @property-read mixed $areAllPlayersDisqualified
 * @property-read mixed $currentQuestion
 * @property-read mixed $currentQuestionNumber
 * @property-read mixed $isOver
 * @property-read mixed $numberOfQuestions
 * @property-read mixed $secondsSinceStarted
 * @property-read mixed $secondsUntilStart
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereSecondsPerQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Game whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Game extends Model
{
    protected $fillable = ['startTime','secondsPerQuestion','currentQuestionNumber'];
    const NO_QUESTION_NUMBER = 0;

    function questions()
    {
        return $this->hasMany(Question::class);
    }

    function users()
    {
        return $this->belongsToMany(User::class);
    }

    static function createNewGame($secondsUntilStart = 120, $secondsPerQuestion = 10)
    {
        $newGame = new Game;
        $newGame->startTime = Carbon::now()->addSeconds($secondsUntilStart);
        $newGame->secondsPerQuestion = $secondsPerQuestion;

        $newGame->save();

        //TODO: Add questions to the game
        for($i = 1; $i <= 12; $i++)
        {
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
        if($this->secondsSinceStarted < 0)
        {   // Game hasn't started yet
            return static::NO_QUESTION_NUMBER;
        }

        if ($this->secondsSinceStarted > $this->secondsPerQuestion * $this->questions->count())
        {   // Game has ended already
            return static::NO_QUESTION_NUMBER;
        }

        return floor($this->secondsSinceStarted / $this->secondsPerQuestion)+1;
    }

    static function upcomingGame()
    {
        $latestGame = static::currentGame();

        if($latestGame->secondsUntilStart < 0)
        {
            return null;
        }

        return $latestGame;
    }

    static function currentGame($userId = null)
    {
        if ($userId == null)
        {
            return static::orderBy('id','DESC')->first();
        }


        $gameId = DB::table('games')
            ->join('game_user', 'games.id', '=', 'game_user.game_id')
            ->where('game_user.user_id', '=', $userId)
            ->orderBy('games.id','DESC')
            ->pluck('games.id')
            ->first();

        return static::find($gameId);

    }

    function getCurrentQuestionAttribute()
    {
        if ($this->currentQuestionNumber === static::NO_QUESTION_NUMBER)
        {
            return null;
        }

        return $this->questions()
            ->orderBy('id','ASC')
            ->skip($this->currentQuestionNumber-1)
            ->first();
    }

    function getIsOverAttribute()
    {
        // The time for all the questions has finished or all players already lost the game
        if(($this->secondsSinceStarted > 0 && $this->currentQuestionNumber === static::NO_QUESTION_NUMBER)
            || $this->areAllPlayersDisqualified)
        {
            return true;
        }

        return false;
    }

    function getAreAllPlayersDisqualifiedAttribute()
    {
        foreach($this->users as $player)
        {
            if (!$player->isDisqualified)
            {   // At least one player is still playing
                return false;
            }
        }

        return true;
    }

    function getNumberOfQuestionsAttribute()
    {
        return $this->questions()->count();
    }


    public function getSecondsUntilStartAttribute(){

        // TODO: Replace back with Carbon::now
        //$fakeNow = new Carbon('11:12:00');
        //$fakeNow = new Carbon('11:12:24');
        $fakeNow = Carbon::now();
        return $fakeNow->diffInSeconds($this->startTime,false);
    }

    public function getSecondsSinceStartedAttribute(){
        return (-1)*$this->secondsUntilStart;
    }

}
