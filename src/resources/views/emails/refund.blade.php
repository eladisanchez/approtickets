@extends('emails.template')

@section('content')
    {{-- Cancel·lació --}}

    @if (!$refund->session_new)
        <p>Hola {{ $refund->order->name }},</p>

        <p>Ens posem en contacte amb vosaltres per informar-vos que l'esdeveniment o la visita
            <strong>{{ $refund->product->title }}</strong> del dia {{ $refund->session_canceled->format('d-m-Y') }} a les
            {{ $refund->session_canceled->format('H:i') }} h s'ha cancel·lat.
        </p>

        <p>Feu clic a l'enllaç següent per generar la devolució de {{ $refund->total }} euros corresponent a les entrades
            que vau adquirir per a aquest esdeveniment o visita. La devolució es farà a la mateixa targeta de crèdit
            utilitzada per fer el pagament.</p>

        <p><a href="{{ route('refund', ['hash' => $refund->hash]) }}">{{ route('refund', ['hash' => $refund->hash]) }}</a>
        </p>

        <p>Rebeu aquest missatge perquè teniu entrades per a <strong>{{ $refund->product->title }}</strong>. Us demanem
            que feu arribar aquesta informació a les persones que us acompanyin.</p>

        <p>Si teniu cap dubte, podeu enviar un correu a turisme@turismesolsones.com o bé trucar al 973 48 23 10.</p>

        <p>Moltes gràcies per la vostra comprensió.</p>

        {{-- Canvi de dia --}}
    @else
        <p>Hola {{ $refund->order->name }},</p>

        <p>Ens posem en contacte amb vosaltres per informar-vos que l'esdeveniment o la visita
            <strong>{{ $refund->product->title }}</strong> del dia {{ $refund->session_canceled->format('d-m-Y') }} a
            les {{ $refund->session_canceled->format('H:i') }} h se suspèn i es trasllada al dia
            {{ $refund->session_new->format('d-m-Y') }} a les {{ $refund->session_new->format('H:i') }}.
        </p>

        <p>Com que teníeu entrades comprades per a aquesta activitat, podeu:</p>

        <p><strong>- Acceptar el canvi de data per al dia {{ $refund->session_new->format('d-m-Y') }} a les
                {{ $refund->session_new->format('H:i') }}</strong> (us serviran les mateixes entrades).</p>

        <p>Podeu descarregar de nou el PDF de les entrades en aquest enllaç:<br><a
                href="{{ route('order.pdf', [$refund->order->session, $refund->order->id]) }}">{{ route('order.pdf', [$refund->order->session, $refund->order->id]) }}</a>
        </p>

        <p>Entenem que a hores d'ara potser no sabeu si us va bé la nova data; en aquest cas, si finalment no podeu assistir
            a l'activitat, us faríem la devolució més endavant.</p>

        <p><strong>- Sol·licitar la devolució de l'import de la compra.</strong></p>

        <p>Si voleu sol·licitar la devolució, feu clic a l'enllaç següent per generar la devolució de
            {{ $refund->total }} euros corresponent a les entrades que vau adquirir per a aquest esdeveniment o visita.
            La devolució es farà a la mateixa targeta de crèdit utilitzada per fer el pagament.</p>

        <p><a href="{{ route('refund', ['hash' => $refund->hash]) }}">{{ route('refund', ['hash' => $refund->hash]) }}</a>
        </p>

        <p>Rebeu aquest missatge perquè teniu entrades per a {{ $refund->product->title }}. Us demanem que feu arribar
            aquesta informació a les persones que us acompanyin.</p>

        <p>Si teniu cap dubte, podeu enviar un correu a turisme@turismesolsones.com o bé trucar al 973 48 23 10.</p>

        <p>Moltes gràcies per la vostra comprensió.</p>
    @endif
@endsection
