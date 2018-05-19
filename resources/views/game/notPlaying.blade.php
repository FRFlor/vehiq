@extends('layouts.app')

@section('content')
    <div v-if="gameState === 'waiting'">
        @{{gameState}}
        <game-timer :start-seconds="5" @time-expired="updateState('playing')"></game-timer>
    </div>

    <div v-if="gameState === 'playing'">
        Question comes here
    </div>

    <div v-if="gameState === 'over'">
        <h1>The game is over</h1>

        <h2>Score</h2>
        <p>{{Auth::user()->name}}: {{Auth::user()->score}}</p>
    </div>

@endsection
