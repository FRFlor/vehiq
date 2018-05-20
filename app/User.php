<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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

    function games()
    {
        return $this->belongsToMany(Game::class);
    }

    function questions()
    {
        return $this->belongsToMany(Question::class)->withPivot('answerGiven');
    }

    static function findBySecretToken($secret)
    {
        // Student Note: Ideally Eloquent Relationship should be used here
        // but I didn't want to create a Model for the oauth_client. Also,
        // It's nice to see usage of the DB object as well
        $userId = DB::table('users')
            ->join('oauth_clients', 'users.id', '=', 'oauth_clients.user_id')
            ->where('oauth_clients.secret', '=', $secret)
            ->pluck('users.id')
            ->first();

        return static::find($userId);
    }

    function getScoreAttribute()
    {
        $gameId = Game::currentGame($this->id)->id;

        $score = 0;
        foreach($this->questions as $userResponse)
        {
            if($userResponse->game_Id == $gameId &&
                trim($userResponse->rightAnswer) == trim($userResponse->pivot->answerGiven))
            {
                $score++;
            }
        }

        return $score;
    }


    function getIsDisqualifiedAttribute()
    {
        $gameId = Game::currentGame($this->id)->id;

        $isDisqualified = false;
        foreach($this->questions as $userResponse) {
            if ($userResponse->game_Id == $gameId &&
                trim($userResponse->rightAnswer) != trim($userResponse->pivot->answerGiven)) {
                $isDisqualified = true;
                break;
            }
        }

        return $isDisqualified;
    }


    function answerQuestion($answerGiven)
    {
        return $this->questions()
            ->save(Game::currentGame($this->id)->currentQuestion,
            ['answerGiven' => $answerGiven]);
    }
}
