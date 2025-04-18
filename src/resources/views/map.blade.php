<html>

<head>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            @page {
                size: landscape
            }
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
        }

        h2 {}

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            max-width: 1200px;
            margin: 0 auto;
        }

        table th {
            width: 70px;
            text-align: center;
            background-color: #FFFF8B;
            border: 1px solid #000;
        }

        table td {
            padding: 6px 0;
            text-align: center;
        }

        .si {
            background: #FFF;
            border: 1px solid #000;
        }

        .reserva {
            background: #b3b3b3;
            border: 2px solid #000;
        }

        .blocked {
            background: rgb(252, 195, 195);
        }

        .escenari {
            padding: 10px;
            background: #b3b3b3;
            color: #FFF;
            border: 1px solid #000;
            font-size: 18px;
            width: 600px;
            margin: 20px auto 0 auto;
        }
    </style>
</head>

<body>

    <h2>{{ $tickets->product->title }}
        {{ $tickets->day->format('d/m/Y') }}
        {{ $tickets->hour->format('H.i \h') }}
    </h2>

    <table id="planol-reserva">

    </table>

    <div class="escenari">ESCENARI</div>

    <script>
        const seats = @json($tickets->seats);
        const bookings = @json($tickets->bookings);
        const distancia_social = {{ $tickets->product->distancia_social ? 'true' : 'false' }};
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script>
        jQuery(document).ready(function($) {

            if ($("#planol-reserva").length) {

                var arrX = $.map(seats, function(o) {
                    return o.x;
                });
                var arrY = $.map(seats, function(o) {
                    return o.y;
                });
                var files = $.map(seats, function(o) {
                    return o.f;
                });
                arrX = $.unique(arrX);
                arrY = $.unique(arrY);
                files = $.unique(files);
                var maxX = Math.max.apply(this, arrX);
                var minX = Math.min.apply(this, arrX);
                var maxY = Math.max.apply(this, arrY);
                var minY = Math.min.apply(this, arrY);

                var tableMarkup = '';
                var x, y, i = 0;

                for (x = minX; x <= maxX; x++) {
                    tableMarkup += "<tr><th>FILA " + files[i] + "</th>";
                    for (y = minY; y <= maxY; y++) {
                        tableMarkup += "<td data-x='" + x + "' data-y='" + y + "'>&nbsp;</td>";
                    }
                    tableMarkup += "<th>FILA " + files[i] + "</th></tr>";
                    i++;
                }

                $("#planol-reserva").html(tableMarkup);
                const loadSeats = function(bookings) {
                    $("#planol-reserva td").removeClass('si');
                    $.each(seats, function(i, e) {
                        $('#planol-reserva td[data-x="' + e.x + '"][data-y="' + e.y + '"]')
                            .addClass("si")
                            .attr("data-s", e.s)
                            .attr("data-f", e.f)
                            .attr("data-z", e.z).html(e.s);
                    });
                };
                loadSeats(bookings);


                if (typeof bookings !== 'undefined') {
                    $.each(bookings, function(i, e) {
                        $seient = $('#planol-reserva td[data-s="' + e.seat + '"][data-f="' + e.row + '"]');
                        if (distancia_social) {
                            if ($seient.prev().hasClass('si') && !$seient.prev().hasClass('reserva')) {
                                $seient.prev().addClass('blocked');
                                if ($seient.prev().prev().hasClass('si') && !$seient.prev().prev().hasClass(
                                        'reserva')) {
                                    $seient.prev().prev().addClass('blocked');
                                }
                            }
                            if ($seient.next().hasClass('si') && !$seient.next().hasClass('reserva')) {
                                $seient.next().addClass('blocked');
                                if ($seient.next().next().hasClass('si') && !$seient.next().next().hasClass(
                                        'reserva')) {
                                    $seient.next().next().addClass('blocked');
                                }
                            }
                            $seient.removeClass('blocked');
                        }
                        $seient.addClass('reserva');
                    });
                }

            }
        });
    </script>


</body>

</html>
