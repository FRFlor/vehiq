@extends('layouts.base')

@section('content')
    <h4>{{$questionJson->statement}}</h4>
    <br/>
    <ul class="list-group">
        @foreach($questionJson as $alternative)
            @if($alternative === $questionJson->statement || $alternative === $questionJson->id)
                @continue
            @endif
            <li class="list-unstyled">

                <?php $onClickCall = "tryAnswer('" . $questionJson->id . "','" . $alternative . "')";                    ?>

                <button class="btn-sm btn-block" onclick="{{$onClickCall}}">{{$alternative}}</button>


            </li>
        @endforeach
    </>



    <script> function tryAnswer(questionId, answerStr) {
            var isAnswerRight = false;

            axios.get("/api/question/testAnswer", {
                params: {id: questionId, answer: answerStr},
            })
                .then(function (response) {
                    isAnswerRight = response.data.isAnswerRight;
                    if (isAnswerRight)
                    {
                        window.location.replace('/play');
                    }
                    else
                    {
                        window.location.replace('/');
                    }
                })
        }
    </script>
@endsection
