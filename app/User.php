<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


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
