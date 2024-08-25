<x-filament-widgets::widget>
    <x-slot name="heading">Localitats</x-slot>
    <style>
        .map {
            width: 100%;
            display: grid;
            gap: 1px;
            background: #eee;
            border: 1px solid #eee;
            font-family: 'arial', sans-serif;
            grid-template-columns: repeat(90, 1fr);
            grid-template-rows: repeat(90, 1fr);
        }

        .seat {
            aspect-ratio: 1;
            background: #FFF;
            position: relative;
            font-size: 5px;
            line-height: 5px;
            text-align: center;
            cursor: pointer;
        }

        .seat.selected {
            background: #deec9a;
        }

        .seat strong,
        .seat span {
            display: block;
        }
    </style>
    <x-filament::section>
        <div class="gap-4">
            <div class="map" id="seatMap">
                @foreach ($gridItems as $square)
                    @php
                        $squareSeat = $this->isSeat($square);
                    @endphp
                    <div class="seat {{ $squareSeat ? 'selected' : '' }}"
                        data-x="{{ $square['x'] }}"
                        data-y="{{ $square['y'] }}"
                        style="grid-row: {{ $square['x'] }}; grid-column: {{ $square['y'] }};">
                        @if ($squareSeat)
                            <span>{{ $squareSeat['f'] }}</span>
                            <strong>{{ $squareSeat['s'] }}</strong>
                        @endif
                    </div>
                @endforeach
            </div>
            <div style="width: 300px;">
                <div class="flex gap-3 mb-4">
                    <div>
                        <label>Fila</label>
                        <x-filament::input.wrapper>
                            <x-filament::input label="Fila" type="number" wire:model.lazy="row" />
                        </x-filament::input.wrapper>
                    </div>
                    <div>
                        <label>Seient</label>
                        <x-filament::input.wrapper>
                            <x-filament::input label="Seient" type="number" wire:model.lazy="seat" />
                        </x-filament::input.wrapper>
                    </div>
                </div>
                <div class="flex gap-3">
                    <x-filament::button wire:click="handleSave">Desa el plànol</x-filament::button>
                    <x-filament::button wire:click="handleDelete" color="danger" icon="heroicon-m-trash">Esborra-ho
                        tot</x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
    
    <!-- JavaScript to handle seat selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const seatMap = document.getElementById('seatMap');
            const selectedSeats = new Set();

            // Ensure Livewire is loaded
            if (typeof Livewire === 'undefined') {
                console.error('Livewire is not loaded. Please make sure Livewire is properly set up.');
                return;
            }

            seatMap.addEventListener('click', function (e) {
                if (e.target.classList.contains('seat')) {
                    const x = e.target.dataset.x;
                    const y = e.target.dataset.y;
                    const seatKey = `${x}-${y}`;

                    if (selectedSeats.has(seatKey)) {
                        selectedSeats.delete(seatKey);
                        e.target.classList.remove('selected');
                    } else {
                        selectedSeats.add(seatKey);
                        e.target.classList.add('selected');
                    }

                    console.log('Selected seats:', selectedSeats); // Log for debugging

                    // Send bulk updates to Livewire after modifications
                    sendBulkUpdate(selectedSeats);
                }
            });

            function sendBulkUpdate(seatSet) {
                const seats = Array.from(seatSet).map(key => {
                    const [x, y] = key.split('-');
                    return { x: parseInt(x), y: parseInt(y) };
                });

                console.log('Sending seats to Livewire:', seats); // Log for debugging

                // Emit event to Livewire with the updated seats
                Livewire.emit('handleSelectBulk', seats);
            }
        });
    </script>
</x-filament-widgets::widget>