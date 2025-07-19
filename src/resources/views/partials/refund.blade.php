@if ($refund->refund)

    <p>La devolució s'ha completat.</p>
@else
    <p>Accedint al següent enllaç se't farà la devolució de <strong>{{ $refund->total }} €</strong> a la targeta de
        crèdit corresponents a les teves entrades per els esdeveniments cancel·lats.</p>

    <p>La devolució no és immediata i pot trigar un màxim de 14 dies laborables des de la seva autorització, depenent
        del tipus de targeta i l'entitat bancària.</p>

    <ul>
        @foreach ($refund->bookings() as $booking)
            @if ($booking->refund && $booking->price)
                <li class="list-group-item">
                    <strong>{{ $booking->product->title }}</strong> - {{ $booking->day->format('d/m') }} a les
                    {{ $booking->hour }} h<br>
                    {{ $booking->tickets }} x {{ $booking->rate->title }}:
                    {{ number_format($booking->total, 2, ',', '.') }} &euro;
                    @if ($booking->seat)
                        - {{ $booking->formattedSeat }}
                    @endif
                </li>
            @endif
        @endforeach
    </ul>

    <form action="{{ $tpv->getPath('/realizarPago') }}" method="post">
        {!! $tpv->getFormHiddens() !!}
        <button class="button">Efectua la devolució</button>
    </form>

@endif
