<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer $id
 * @property string $nickname
 * @property integer $score
 * @property boolean $isDisqualified
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Player extends Model
{
    function registerNewPlayer($nickname)
    {
        $isSuccessful = false;

        $newcomer = new Player();
        // Player nickname cannot be empty
        if (strlen(trim($nickname)) != 0)
        {
            $newcomer->nickname = $nickname;
            $isSuccessful = $newcomer->save();
        }

        return $isSuccessful;
    }

    function incrementScore()
    {
        $this->score++;
        return $this->save();
    }

    function disqualify()
    {
        $this->isDisqualified = true;
        return $this->save();
    }

    static function scopeQualified($query)
    {
        return $query->where('isDisqualified',false);
    }

    static function topScore()
    {
        $topPlayer = static::qualified()->orderby('score','DESC')->take(1)->get();

        $highScore = 0;

        // If there is a top player
        if ($topPlayer->count() > 0)
        {
            $highScore = $topPlayer[0]->score;
        }

        return $highScore;
    }

    static function lead()
    {
        $highScore = static::topScore();

        // There might be more than 1 player on the lead
        return static::qualified()->where('score',$highScore)->get();
    }

}
