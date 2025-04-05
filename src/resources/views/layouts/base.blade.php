<!doctype html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    @section('head')
        <title>{{ config('app.name') }}</title>
    @show
</head>

<body>

    <header class="cap">

        <div class="wrapper">

            <div class="titol">
                <a class="ico-cistell" href="{{ route('cart') }}">

                </a>
                <a href="http://cardona.cat/" class="logo">Visita
                    Cardona</a>
            </div>

            {{-- Cistell desplegable
            @include('parts.cistell') --}}

            <h1><a href="{{ route('home') }}">{{ config('app.name') }}</a></h1>

        </div>

    </header>

    <section class="contingut">

        @yield('content')

    </section>

    <footer class="peu">

        <div class="wrapper">

            <p>Per qualsevol dubte, podeu escriure a <a href="mailto:cardona@cardona.cat">cardona@cardona.cat</a> o <a
                    href="mailto:llimonacm@cardona.cat">llimonacm@cardona.cat</a></p>

        </div>

    </footer>

</body>

</html>
