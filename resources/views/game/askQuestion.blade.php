@extends('layouts.app')

@section('content')
    <p>{{Auth::user()->name}}: {{Auth::user()->score}}</p>
    <?php
    $question = App\Game::currentGame()->currentQuestion;
    ?>

    @if(!Auth::user()->isDisqualified)
        <h1>Question {{$questionData['questionNumber']}}:</h1>
        <h4>{{$questionData['statement']}}</h4>
        <br/>
        <ul class="list-group">
            @foreach($questionData['choices'] as $alternative)
                <li class="list-unstyled">
                    <?php $onClickCall = "tryAnswer('" . $questionData['id'] . "','" . $alternative . "')"; ?>
                    <button class="btn-sm btn-block m-1" onclick="{{$onClickCall}}">{{$alternative}}</button>
                </li>
            @endforeach
        </ul>
    @endif


    <script>
        function tryAnswer(questionId, answerStr) {

            window.axios.post("/game/answerQuestion",
                {
                    answerGiven: answerStr
                }).then(function (response) {
                if (response.status === 200) {
                    window.location.replace('/play');
                }
                else {
                    alert("Api Failed to respond!");
                }
            })
        }
    </script>
@endsection
