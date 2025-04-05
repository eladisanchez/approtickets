@extends('layouts.base')

@section('head')
    <title>{{ trans('textos.cistell') }} - {{ trans('textos.titol') }}</title>
@stop

@section('content')

    <div class="wrapper">

        <h2>Cistell</h2>

        @if (Session::get('error'))
            <p class="error">{{ Session::get('error') }}</p>
        @endif

        @isset($pendingOrder)
            <p class="error">Hi ha una comanda pendent de pagament. <a
                    href="{{ route('order.payment', ['id' => $pendingOrder->id]) }}">Reintentar el pagament amb targeta</a></p>
        @endisset

        @if ($cart->count() > 0)

            <table class="cistell">

                <thead>
                    <tr>
                        <th>{{ trans('textos.producte') }}</th>
                        <th>{{ trans('textos.tarifa') }}</th>
                        <th>{{ trans('textos.dia_hora') }}</th>
                        <th>{{ trans('textos.quantitat') }}</th>
                        @if (!Auth::user())
                            <th>{{ trans('textos.preu') }}</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($cart as $row)
                        {{-- PRODUCTES SIMPLES --}}
                        @if (!$row->product->is_pack)
                            <tr class="row-producte">
                                <td class="producte">
                                    @if (file_exists('images/' . $row->product->name . '.jpg'))
                                        <img src="{{ url('img/small/' . $row->product->name) }}.jpg" class="small">
                                    @endif
                                    <br><strong>{{ $row->product->title }}</strong>
                                </td>
                                <td class="td-tarifa">
                                    {{ $row->rate->title }}
                                    @if ($row->seat)
                                        <br> <strong>{{ $row->formattedSeat }} </strong>
                                    @endif
                                </td>
                                <td class="td-data">
                                    {{ $row->day->format('d/m/Y') }} - {{ $row->hour }} h
                                </td>
                                <td class="td-qty">{{ $row->tickets }}</td>

                                <td class="td-preu">{{ number_format($row->price, 2, ',', '.') }} €</td>

                                {{-- <td class="td-creu"><a href="{{action('cart.remove',array('rowid' => $row->rowid))}}" class="creu">Elimina</a></td> --}}
                            </tr>

                            {{-- PACKS --}}
                        @else
                            <tr class="row-producte">
                                <td class="producte">
                                    @if (file_exists('images/' . $row->options->nom . '.jpg'))
                                        <img src="{{ url('img/small/' . $row->options->nom) }}.jpg" class="small">
                                    @endif
                                    <br><strong>{{ $row->producte->titol }}</strong>
                                </td>
                                <td class="td-tarifa">{{ $row->options->tarifa->titol }}</td>
                                <td class="td-data">&nbsp;</td>
                                <td class="td-qty">{{ $row->qty }}</td>

                                <td class="td-preu">{{ number_format($row->price, 2, ',', '.') }} €</td>
                                <td class="td-subtotal">{{ number_format($row->subtotal, 2, ',', '.') }} €</td>

                                <td class="td-creu"><a href="{{ action('cistell.remove', ['rowid' => $row->rowid]) }}"
                                        class="creu">Elimina</a></td>
                            </tr>

                            @foreach ($row->options->reserves as $reserva)
                                <tr class="row-producte sub">
                                    <td class="producte">
                                        {{ Producte::find($reserva['producte'])->titol }}
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>
                                        {{ Carbon::createFromFormat('Y-m-d', $reserva['dia'])->format('d/m/Y') }} -
                                        {{ Carbon::createFromFormat('H:i:s', $reserva['hora'])->format('H:i') }} h
                                    </td>
                                    <td>&nbsp;</td>

                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>

                                    <td>&nbsp;</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    <tr>
                        <td colspan="6" class="total">

                            Total: <strong>{{ $total }} €</strong>

                        </td>
                        <td class="total">&nbsp;</td>
                    </tr>

                </tbody>
            </table>

            @if (Session::has('codi'))
                <p class="avis-codi"><strong>{{ trans('textos.codi_promocional') }}</strong>
                    {{ Session::get('codi.nom') }}</p>
            @else
                {{-- <div class="entra-codi">
                    
                    {{ Form::open(array('route'=>'codi')) }}

                        {{ Form::label('codi',trans('textos.codi_promocional'))}}
                        {{ Form::text('codi') }}

                        {{ Form::submit(trans('textos.valida_codi')) }}
                        {{ Form::text('textos.aplicacio_codi') }}

                    {{ Form::close() }}
                    
                </div> --}}
            @endif

            <div class="row-accions-cistell">
                <a href="{{ route('home') }}" class="boto">{{ trans('textos.continua_comprant') }}</a>
                <a href="{{ route('cart.destroy') }}" class="boto">{{ trans('textos.buida_cistell') }}</a>
                <a href="#" class="boto boto-confirmar">{{ trans('textos.finalitzar_comanda') }}</a>
            </div>
        @else
            <p class="error">{{ trans('textos.cistell_buit') }}</p>

        @endif


        <div class="confirmacio @isset($errors) @if ($errors->has('errors')) desplegat @endif @endisset"
            id="confirmacio">

            <h2>{{ trans('textos.confirma_la_comanda') }}</h2>

            <section class="passos">

                <div class="dades-client">

                    <h3>{{ trans('textos.dades_del_client') }}</h3>
                    @isset($errors)
                        @if ($errors->has('errors'))
                            <p class="error">
                                <strong>{{ trans('textos.errors_de_validacio') }}</strong><br>
                                @foreach ($errors->all() as $error)
                                    - {{ $error }}<br>
                                @endforeach
                            </p>
                        @endif
                    @endisset

                    <form action="{{ route('order.store') }}" method="POST">

                        @csrf

                        <p>
                            <label for="name">{{ trans('textos.nom_cognoms') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}">
                        </p>

                        <p>
                            <label for="email">{{ trans('textos.email') }}</label>
                            <input type="text" name="email" id="email" value="{{ old('email') }}">
                        </p>

                        <p>
                            <label for="phone">{{ trans('textos.telefon') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}">
                        </p>

                        <p>
                            <label for="cp">{{ trans('textos.cp') }}</label>
                            <input type="text" name="cp" id="cp" value="{{ old('cp') }}">
                        </p>

                        <p>
                            <label for="observations">{{ trans('textos.observacions') }}</label>
                            <textarea name="observations" id="observations">{{ old('observations') }}</textarea>
                        </p>

                        @if ($total > 0)

                            <p>{{ trans('textos.metode_pagament') }} <strong>

                                    @if (auth()->user())
                                        <label style="margin-right: 15px">
                                            <input type="radio" name="payment" value="credit" checked>
                                            {{ trans('textos.credit') }}</label>
                                        <label>
                                            <input type="radio" name="payment" value="cash">
                                            {{ trans('textos.efectiu') }}</label>
                                        <label><input type="radio" name="payment" value="card">
                                            {{ trans('textos.tarjeta_credit') }}</label>
                                        {{-- <label><input type="radio" name="payment" value="santander" checked>
                                            Santander</label> --}}
                                    @else
                                        <input type="hidden" name="pagament" value="card">
                                        {{ trans('textos.tarjeta_credit') }}
                                    @endif

                                </strong></p>
                        @else
                            <input type="hidden" name="pagament" value="credit">

                        @endif

                        <p><label>
                                <input required type="checkbox" name="conditions" id="condicions" value="1">
                                {{ trans('textos.he_llegit_i_accepto_les') }} <a
                                    href="{{ route('page', ['slug' => 'condicions-us']) }}"
                                    target="_blank">{{ trans('textos.condicions_dus') }}</a>
                            </label></p>

                        <p>
                            <input type="submit" value="{{ trans('textos.confirma_la_comanda') }}" class="boto-confirma">
                        </p>


                    </form>

                    <a href="{{ route('cart') }}" class="tornar-cistell">{{ trans('textos.torna_al_cistell') }}</a>

                </div>

            </section>

        </div>

    </div>


@stop
