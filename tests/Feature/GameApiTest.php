<?php

namespace Tests\Feature;

use App\Game;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class GameApiTest extends TestCase
{

    // Will the API properly update the counter column (rightAnswerCounter, wrongAnswer1Counter, ...) at the correct question row?
    public function testItUpdatesTheChoicesCounters()
    {

    }
}
