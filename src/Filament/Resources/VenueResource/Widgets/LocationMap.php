<?php

namespace ApproTickets\Filament\Resources\VenueResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentAsset;

class LocationMap extends Widget
{
    protected static string $view = 'approtickets::filament.resources.venue-resource.widgets.location-map';
    protected int|string|array $columnSpan = 'full';

    public ?Model $record = null;
    public $map = [];
    public $mouseDown = false;
    public $erase = false;
    public $seat = 1;
    public $row = 1;
    public $zone = 1;

    public $gridSize = 90;

    public function mount()
    {
        $this->map = $this->record->seats ?? [];
    }

    public function findSeatIndex($square)
    {
        foreach ($this->map as $index => $seat) {
            if ($seat['x'] == $square['x'] && $seat['y'] == $square['y']) {
                return $index;
            }
        }
        return null;
    }

    public function handleSelect($square)
    {
        // Check if the square already has a seat
        $existingSeatIndex = $this->findSeatIndex($square);

        if ($existingSeatIndex !== null) {
            // Remove the seat if it exists
            unset($this->map[$existingSeatIndex]);
        } else {
            // Add a new seat with the current seat, row, and zone numbers
            $newSeat = $square;
            $newSeat['s'] = $this->seat;
            $newSeat['f'] = $this->row;
            $newSeat['z'] = $this->zone;
            $this->map[] = $newSeat;
            $this->seat++;
        }

        // Re-index the array to keep consistent keys
        $this->map = array_values($this->map);
    }

    public function updatedRow($value)
    {
        $this->seat = 1;
    }

    public function handleDelete()
    {
        $this->map = [];
    }

    public function handleSave()
    {
        $this->record->seats = $this->map;
        $this->record->save();
        Notification::make()
            ->title('Plànol guardat')
            ->body('El plànol s\'ha guardat correctament.')
            ->success()
            ->send();
    }

    public function render(): \Illuminate\View\View
    {
        // Prepare the grid items
        $gridItems = [];
        $seatMap = [];

        // Precompute the seat map for easier lookup
        foreach ($this->map as $seat) {
            $seatMap[$seat['x']][$seat['y']] = ['s' => $seat['s'], 'f' => $seat['f'], 'z' => $seat['z']];
        }

        // Generate the grid with seat information
        for ($y = 1; $y <= $this->gridSize; $y++) {
            for ($x = 1; $x <= $this->gridSize; $x++) {
                $gridItems[] = [
                    'x' => $x,
                    'y' => $y,
                    'seat' => $seatMap[$x][$y] ?? null // Check if there's a seat at this position
                ];
            }
        }

        return view(static::$view)
            ->with('gridItems', $gridItems);
    }

}
