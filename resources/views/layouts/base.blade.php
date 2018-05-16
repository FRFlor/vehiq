<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{asset('css/app.css')}}">

    <title>{{config('app.name','App name not found')}}</title>

    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <style>
        #answerButton{
            margin-top: 10px
        }
    </style>
</head>
<body>
    @include('layouts.includes.navbar')
    <br/>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
