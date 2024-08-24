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
        }

        .seat {
            aspect-ratio: 1;
            background: #FFF;
            position: relative;
            font-size: 9px;
            line-height: 8px;
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
    <x-filament::section>
        <div class="gap-4">
            <div class="map" style="grid-template-columns: repeat(90, 1fr);">
                @foreach ($gridItems as $square)
                    @php
                        $squareSeat = $this->isSeat($square);
                    @endphp
                    <div wire:click="handleSelect({{ json_encode(['s' => $seat, 'f' => $row, 'x' => $square['x'], 'y' => $square['y']]) }})"
                        class="seat {{ $squareSeat ? 'selected' : '' }}"
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
</x-filament-widgets::widget>
