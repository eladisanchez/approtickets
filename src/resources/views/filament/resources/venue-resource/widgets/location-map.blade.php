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
        }

        .seat.selected {
            background: #deec9a;
        }

        .seat strong,
        .seat span {
            display: block;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seatMap = document.getElementById('seatMap');

            seatMap.addEventListener('click', function(e) {
                if (e.target.classList.contains('seat')) {
                    const x = e.target.dataset.x;
                    const y = e.target.dataset.y;

                    // Perform seat selection using JavaScript
                    toggleSeatSelection(x, y);
                }
            });

            function toggleSeatSelection(x, y) {
                // Update the UI first
                const seat = document.querySelector(`.seat[data-x="${x}"][data-y="${y}"]`);
                seat.classList.toggle('selected');

                // Then, send bulk updates to Livewire
                Livewire.emit('updateMap', {
                    x,
                    y
                });
            }
        });
    </script>
    <x-filament::section>
        <div class="gap-4">
            <div class="map">
                @foreach ($gridItems as $square)
                    <div class="seat {{ $this->isSeat($square) ? 'selected' : '' }}" data-x="{{ $square['x'] }}"
                        data-y="{{ $square['y'] }}"
                        style="grid-row: {{ $square['x'] }}; grid-column: {{ $square['y'] }};">
                        @if ($this->isSeat($square))
                            <span>{{ $this->isSeat($square)['f'] }}</span>
                            <strong>{{ $this->isSeat($square)['s'] }}</strong>
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
</x-filament-widgets::widget>
