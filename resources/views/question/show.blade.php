@extends('layouts.app')

@section('content')
    <h1>Showing question {{$question->id}}</h1>

    <h3>{{$question->statement}}</h3>

    <ul>
        <li>{{$question->rightAnswer}}</li>
        <li>{{$question->wrongAnswer1}}</li>
        <li>{{$question->wrongAnswer2}}</li>
    </ul>
@endsection
