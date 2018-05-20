<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    protected $fillable = ['startTime','secondsPerQuestion','currentQuestionNumber'];
    function questions()
    {
        return $this->hasMany(Question::class);
    }

    function users()
    {
        return $this->belongsToMany(User::class);
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


    public function getSecondsUntilStartAttribute(){

        return Carbon::now()->diffInSeconds($this->startTime,false);
    }



    public function gotoNextQuestion()
    {
        $isThereAnotherQuestion = false;

        $this->currentQuestionNumber++;

        if (!$this->isOver)
        {
            $isThereAnotherQuestion = true;
        }

        // goToNextQuestion fails if:
        //  - Writing to the database fails
        //              or
        //  - There are no more questions to be fetched
        return ($this->save() && $isThereAnotherQuestion);
    }
}
