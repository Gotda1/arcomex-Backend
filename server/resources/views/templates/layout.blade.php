<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insight Empresas</title>
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/jquery-ui/css/jquery-ui-1.9.2.custom.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    @isset($styles)
        @foreach ($styles as $style)
        <link rel="stylesheet" href="{{ asset('assets/css/' . $style) }}">
        @endforeach 
    @endisset

</head>

<body>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
            <div class="container">
                <a class="navbar-brand" href="{{ Auth::id() ? route('dashboard') : route('home') }}">
                    <img src="{{ asset('assets/images/Insightempresas_amarillo.png') }}" alt="" class="img-fluid">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    @include("templates.sidebar")
                </div>
            </div>
        </nav>
    
       

        @yield('content')

        @include("templates.footer")


 
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>   
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.min.js') }}"></script> 
    <script src="{{ asset('assets/libs/jquery-ui/js/jquery-ui-1.9.2.custom.min.js') }}"></script>       
    <script src="{{ asset('assets/libs/jquery_validate/jquery.validate.min.js') }}"></script>   
    <script src="{{ asset('assets/libs/jquery_validate/localization/messages_es.min.js') }}"></script>  
    <script src="{{ asset('assets/libs/sweetalert/sweetalert.min.js') }}"></script>
    @isset($scripts)
        @foreach ($scripts as $script)
        <script src="{{ asset('assets/js/' . $script) }}"></script>   
        @endforeach
    @endisset 
</body>

</html>