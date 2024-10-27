<?php

namespace ApproTickets\Http\Controllers;

use ApproTickets\Http\Resources\Category as CategoryResource;
use Illuminate\View\View;
use ApproTickets\Models\Option;
use ApproTickets\Models\Category;
use ApproTickets\Models\Product;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PageController extends BaseController
{

    public function home(): View|InertiaResponse|array
    {
        $siteSections = config('approtickets.sections');
        $products = [];
        $sections = [];
        if ($siteSections):
            foreach ($siteSections as $section=>$sectionName) {
                $sections[$section] = __('approtickets.'.$section);
                $categories = Category::whereHas('products', function ($q) use ($section) {
                    $q->ofTarget($section);
                })->with('products')->orderBy('order', 'asc')->get();
                $products[$section] = CategoryResource::collection($categories);
            }
        else:
            $products = Category::whereHas('products')->with('products')->orderBy('order', 'asc')->get();
        endif;

        // TODO: featured
        $featured = [];

        $props = [
            'sections' => $sections,
            'products' => $products,
            'featured' => $featured
        ];

        //return $props;

        if (config('approtickets.inertia')) {
            return Inertia::render('Home', $props);
        }
        return view('home', $props);
    }

    public function page($slug): View
    {
        $page = Option::where('key', $slug)->firstOrFail();
        return view('page')->with([
            'text' => $page->value,
            'title' => $page->name
        ]);
    }

}
