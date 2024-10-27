<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\View\View;
use ApproTickets\Models\Ticket;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;


class CalendarController extends BaseController
{

    /**
     * Calendar page
     * @return \Illuminate\View\View|\Inertia\Response|array
     */
    public function calendar(): View|InertiaResponse|array
    {
        $today = strtotime("today");
        $nextMonth = date("Y-m-d", strtotime("+2 month", $today));
        $tickets = Ticket::with('product:id,title,summary,place,name')
            ->where('day', '>=', $today)
            ->where('day', '<', $nextMonth)
            ->get();
        $cal = $tickets->filter(function ($item) {
            return $item->product !== null;
        })->map(function ($item) {
            return [
                'uid' => uniqid(),
                'title' => $item->product->title,
                'description' => $item->product->summary,
                'start' => $item->day->format('Y-m-d') . ' ' . $item->hour->format('H:i:s'),
                'location' => $item->product->place,
                'url' => route('product', ['name' => $item->product->name, $item->day->format('Y-m-d'), $item->hour->format('H:i')]),
                'color' => 'blue'
            ];
        })->values();

        if (config('approtickets.inertia')) {
            return Inertia::render('Calendar', [
                'events' => $cal
            ]);
        }

        return view('calendar', [
            'events' => $cal,
        ]);

    }

    /**
     * Generate ICS of all events
     * @return string
     */
    public function ics(): string
    {

        $tickets = Ticket::with('producte:id,title,resum,place,nom,target')
            ->where('dia', '>=', date('Y-m-d'))->get();

        $events = $tickets->map(function ($item) {
            $event = Event::create($item->producte->title)
                ->startsAt(new \DateTime($item->dia->format('Y-m-d') . ' ' . $item->hour))
                ->description($item->producte->resum_ca);
            return $event;
        });

        $calendar = Calendar::create(config('app.name'))
            ->event($events)
            ->get();

        return $calendar;
    }
    
}