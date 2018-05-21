<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;


/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $rememberToken
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Game[] $games
 * @property-read mixed $isCurrentlyInGame
 * @property-read mixed $isDisqualified
 * @property-read mixed $score
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        foreach ($this->questions as $userResponse) {
            if ($userResponse->game_Id == $gameId &&
                trim($userResponse->rightAnswer) == trim($userResponse->pivot->answerGiven)) {
                $score++;
            }
        }

        return $score;
    }


    function getIsDisqualifiedAttribute()
    {
        $currentGame = Game::currentGame($this->id);

        if ($currentGame->secondsUntilStart > 0) {
            return false;
        }

        // Check if there is one question wrong
        foreach ($this->questions as $userResponse) {
            if ($userResponse->game_Id == $currentGame->id &&
                trim($userResponse->rightAnswer) != trim($userResponse->pivot->answerGiven)) {
                return true;
            }
        }

        // Check if the player missed one question
        if ($this->questions->count() < $currentGame->currentQuestionNumber - 1 && $currentGame->currentQuestionNumber != 0) {
            return true;
        }

        return false;
    }


    function answerQuestion($answerGiven)
    {
        // Someone that is disqualified cannot answer questions, only watch them
        if ($this->isDisqualified) {
            return false;
        }

        return $this->questions()
            ->save(Game::currentGame($this->id)->currentQuestion,
                ['answerGiven' => $answerGiven]);
    }

    function getIsCurrentlyInGameAttribute()
    {
        foreach ($this->games as $game) {
            if (!$game->isOver) {
                return true;
            }
        }

        return false;
    }

    function joinGame()
    {
        // Is currently playing a game? Cannot enroll...
        if ($this->isCurrentlyInGame) {
            return false;
        }

        $game = Game::upcomingGame();

        // There are no new games available? Cannot enroll...
        if (!$game) {
            return false;
        }

        return $this->games()->save($game) ? true : false;
    }
}
