<?php

namespace ApproTickets\Filament\Resources\VenueResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentAsset;

class LocationMap extends Widget
{
    protected static string $view = 'approtickets::filament.resources.venue-resource.widgets.location-map';
    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;
    public $map = [];
    public $mouseDown = false;
    public $erase = false;
    public $seat = 1;
    public $row = 1;

    public $gridSize = 90;

    public function mount()
    {
        $this->map = $this->record->seats ?? [];
    }

    public function isSeat($square)
    {
        foreach ($this->map as $seat) {
            if ($seat['x'] == $square['x'] && $seat['y'] == $square['y']) {
                return $seat;
            }
        }
        return null;
    }

    public function handleSelect($square)
    {
        $newMap = $this->map;
        if ($seat = $this->isSeat($square)) {
            $this->map = array_filter($newMap, function ($s) use ($square) {
                return $s['x'] != $square['x'] || $s['y'] != $square['y'];
            });
        } else {
            $newSeat = $this->seat + 1;
            $this->seat = $newSeat;
            $newMap[] = $square;
            $this->map = $newMap;
        }
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
        $gridItems = [];
        for ($y = 1; $y <= $this->gridSize; $y++) {
            for ($x = 1; $x <= $this->gridSize; $x++) {
                $gridItems[] = ['x' => $x, 'y' => $y];
            }
        }
        return view(static::$view)
            ->with('gridItems', $gridItems);
    }

}
