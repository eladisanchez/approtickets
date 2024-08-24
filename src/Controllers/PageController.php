<?php

namespace ApproTickets\Controllers;

use Illuminate\View\View;
use ApproTickets\Models\Option;

class PageController extends Controller
{

    public function __invoke($slug): View
    {
        $page = Option::where('key', $slug)->firstOrFail();
        return view('page')->with(['text' => $page->value, 'title' => $page->name]);
    }

}
