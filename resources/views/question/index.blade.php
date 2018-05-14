@extends('layouts.base')

@section('content')
    <div class="container">
        <h2>Questions</h2>


        <button class="btn-block">Create New</button>

        <ul class="list-group">
            @foreach(App\Question::all() as $question)
                <li class="list-group-item">
                    <a class="btn btn-light btn-sm" href={{"/question/".$question->id}}>Edit</a>

                    <a class="btn btn-light btn-sm">Delete</a>

                    {{$question->statement}}
                    <span class="badge">{{$question->rightAnswer}}</span>
               </li>
            @endforeach
        </ul>
    </div>
    @endsection