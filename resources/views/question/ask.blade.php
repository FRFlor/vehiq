@extends('layouts.app')

@section('content')

    <h4>{{$question->statement}}</h4>
    <br/>
    <ul class="list-group">
        @foreach($question->shuffledAnswers as $alternative)
            <li class="list-unstyled">

                <?php $onClickCall = "tryAnswer('" . $question->id . "','" . $alternative . "')"; ?>

                <button class="btn-sm btn-block m-1" onclick="{{$onClickCall}}">{{$alternative}}</button>


            </li>
        @endforeach
    </ul>



    <script>
        function tryAnswer(questionId, answerStr) {

            axios.get("/api/question/testAnswer", {
                params: {id: questionId, answer: answerStr},
            }).then(function (response) {
                if (response.status == 200) {
                    if (response.data.isAnswerRight) {
                        window.location.replace('/play');
                    }
                    else {
                        window.location.replace('/');
                    }
                }
                else {
                    alert("Api Failed to respond!");
                }
            })
        }


        function registerPlayer()
        {
            alert("HEY!");
            var playerName = window.prompt("Enter your name");

            axios.get("/api/player/register", {
                params: {nick: playerName},
            }).then(function (response) {
                if (response.status == 200) {
                    if (response.data.isAnswerRight) {
                        window.location.replace('/play');
                    }
                    else {
                        window.location.replace('/');
                    }
                }
                else {
                    alert("Api Failed to respond!");
                }

            })
        }
    </script>
@endsection
