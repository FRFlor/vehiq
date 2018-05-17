@extends('layouts.app')

@section('content')
    <h1>The game is over</h1>

    <h2>Score</h2>
    <p>{{Auth::user()->name}}: {{Auth::user()->score}}</p>
@endsection
