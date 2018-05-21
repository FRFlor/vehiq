@extends('layouts.app')

@section('content')
    @guest
        You must login to watch or play the game...
    @else
        <game-session player-name="{{Auth::user()->name}}" url="{{url('')}}"></game-session>
    @endif
@endsection
