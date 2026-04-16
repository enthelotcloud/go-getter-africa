<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Go-Getter Brand Africa') }}
</title>

<link rel="icon" href="{{asset('go-getter-fav-white.png')}}" sizes="any">
<link rel="icon" href="{{asset('go-getter-fav-white.png')}}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{asset('go-getter-fav-white.png')}}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
