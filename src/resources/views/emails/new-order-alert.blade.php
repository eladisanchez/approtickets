<html>

<p>Nova comanda a Entrades Solsonès</p>

<p>
    <strong>Data de la comanda:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
    <strong>Idioma de l'usuari:</strong> {{ $order->lang }}<br>
    <strong>Grup/Escola:</strong> {{ $order->name }}<br>
    <strong>Email:</strong> {{ $order->email }}<br>
    <strong>Telèfon:</strong> {{ $order->phone }}<br>
    <strong>CP:</strong> {{ $order->cp }}<br>
    <strong>Observacions:</strong> {{ $order->observations }}
</p>

<table>

    <tr>
        <th>Activitat</th>
        <th>Dia i hora</th>
        <th>Persones / tarifa</th>
        <th>Subtotal</th>
    </tr>

    @foreach ($order->bookings as $booking)
        <tr>
            <td>
                <strong>{{ $booking->product->title }}</strong>
            </td>
            <td>
                @if (isset($booking->day))
                    {{ $booking->day->format('d/m/Y') }} - {{ $booking->hour }} h
                @endif
            </td>
            <td>
                {{ $booking->tickets }} x {{ $booking->rate->title }}
            </td>
            <td>
                @if ($booking->price > 0)
                    {{ number_format($booking->total, 2, ',', '.') }} €
                @endif
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="3" class="total"><strong>Total: {{ number_format($order->total, 2, ',', '.') }} €</strong></td>
    </tr>

</table>

</html>
