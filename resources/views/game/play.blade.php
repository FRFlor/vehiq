@extends('layouts.app')

@section('content')
    @if(Auth::user() !== null && !Auth::user()->isDisqualified)
        <p>{{Auth::user()->name}}: {{Auth::user()->score}}</p>
        <game-session
                user-name="{{Auth::user()->name}}"
                user-score="{{Auth::user()->score}}"
                url="{{url('')}}"
                :time-per-question="10"
        ></game-session>
    @else
        You shouldn't be here anymore
    @endif



@endsection
