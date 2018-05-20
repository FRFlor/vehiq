<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

        if ($this->isOver)
        {   // Game has ended already
            return static::NO_QUESTION_NUMBER;
        }

        return floor($this->secondsSinceStarted / $this->secondsPerQuestion);
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
        if($this->secondsSinceStarted > $this->secondsPerQuestion * $this->questions->count() ||
            $this->areAllPlayersDisqualified)
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

    function getAllQuestionsAttribute()
    {
        return $this->questions()->orderBy('id','ASC')->get();
    }

    function getNumberOfQuestionsAttribute()
    {
        return $this->questions()->count();
    }


    public function getSecondsUntilStartAttribute(){

        return Carbon::now()->diffInSeconds($this->startTime,false);
    }

    public function getSecondsSinceStartedAttribute(){
        return (-1)*$this->secondsUntilStart;
    }

}
