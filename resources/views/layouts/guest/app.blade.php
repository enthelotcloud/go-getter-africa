<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{config('app.name')}}</title>
    <link rel="icon" href="{{asset('go-getter-fav-white.png')}}" sizes="any">
    <link rel="icon" href="{{asset('go-getter-fav-white.png')}}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{asset('go-getter-fav-white.png')}}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body>
    @include('layouts.guest.header')
    <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 text-white overflow-hidden">
        @yield('content')
    </div>
    @include('layouts.guest.footer')
    @livewireScripts
</body>
</html>
