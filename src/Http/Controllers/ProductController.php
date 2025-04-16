<?php

namespace ApproTickets\Http\Controllers;

use ApproTickets\Http\Resources\ProductThumbnail;
use Illuminate\Http\RedirectResponse;
use ApproTickets\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Encoders\WebpEncoder;
use ApproTickets\Http\Resources\Product as ProductResource;
use ApproTickets\Http\Resources\Ticket as TicketResource;
use ApproTickets\Http\Resources\Rate as RateResource;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends BaseController
{

	public function show($name, $day = NULL, $hour = NULL): View|RedirectResponse|InertiaResponse|array
	{

		$product = Product::withoutGlobalScopes()->where('name', $name)->firstOrFail();

		if (!auth()->user() || auth()->user()->hasRole('organizer') && auth()->user()->id != $product->user_id) {
			if (!$product || !$product->active)
				abort(404);
		}

		if ($product->is_pack) {

			if (session()->has("pack{$product->id}")) {
				foreach ($product->products as $subproduct) {
					if (!session()->has("pack{$product->id}.bookings.{$subproduct->id}")) {
						return redirect()->route('product', [
							'name' => $product->name
						]);
					}
				}
			}
			if (config('approtickets.inertia')) {
				return Inertia::render('Pack', [
					'pack' => new ProductResource($product),
					'rates' => RateResource::collection($product->rates),
					'products' => ProductThumbnail::collection($product->packProducts),
					'bookingPack' => session()->get('pack')
				]);
			}
			return view('pack', [
				'pack' => $product
			]);

		}

		$availableTickets = $product->nextTickets;

		if (!$hour && $availableTickets->count() == 1) {
			return redirect()->route('product', [
				'name' => $product->name,
				'day' => $availableTickets[0]->day->format('Y-m-d'),
				'hour' => $availableTickets[0]->hour->format('H:i'),
			]);
		}

		$availableDays = $product->availableDays();

		if (!$day && $availableDays->count() == 1) {
			return redirect()->route('product', [
				'name' => $name,
				'day' => $availableDays->first()->format('Y-m-d')
			]);
		}

		if ($day && $hour) {
			$minutesBeforeClose = 60 * $product->hour_limit;
			$closingTime = Carbon::parse("{$day} {$hour}")->subMinutes($minutesBeforeClose);
			if (now() > $closingTime) {
				if (config('approtickets.inertia')) {
					return Inertia::render('Product', [
						'product' => new ProductResource($product)
					]);
				}
				return view('product', [
					'product' => $product,
				]);
			}
		}

		if (config('approtickets.inertia')) {
			$props = [
				'product' => new ProductResource($product),
				'availableDays' => $availableDays,
				'tickets' => TicketResource::collection($product->nextTickets),
				'rates' => RateResource::collection($product->rates),
				'day' => $day,
				'hour' => $hour,
				'bookingPack' => session()->get('pack')
			];
			return Inertia::render('Product', $props);
		}

		return view('product', [
			'product' => $product,
			'availableDays' => $availableDays,
			'tickets' => $day ? $product->ticketsDay($day, $hour) : null,
			'rates' => $product->rates,
			'day' => $day,
			'hour' => $hour,
			'productCart' => $product->inCart()
		]);

	}

	public function availability($id, $day, $hour): array
	{
		$product = Product::findOrFail($id);
		$tickets = $product->ticketsDay($day, $hour);
		return [
			'cart' => $tickets->cartSeats,
			'booked' => $tickets->bookedSeats,
		];
	}

	public function search(): View|InertiaResponse
	{
		$keyword = request()->input('s');
		$products = Product::where('active', 1)
			->where('title', 'like', "%{$keyword}%")->get();
		// ->orWhere('description', 'like', "%{$keyword}%")
		// ->get();
		if (config('approtickets.inertia')) {
			return Inertia::render('Search', [
				'products' => ProductThumbnail::collection($products),
				'keyword' => $keyword
			]);
		}
		return view('search', [
			'products' => $products,
			'keyword' => $keyword
		]);
	}

	public function previewPdf($id)
	{
		$product = Product::find($id);
		$pdf = Pdf::setOptions(['isRemoteEnabled' => true])->loadView(
			'pdf.order-preview',
			[
				'product' => $product
			]
		);
		return $pdf->stream("preview-{$id}.pdf");
	}

	public function image($path)
	{

		$cacheKey = 'image_' . md5($path);

		$cachedImage = Cache::remember($cacheKey, 60, function () use ($path) {
			if (Storage::disk('public')->exists($path)) {
				$directory = explode('/', $path);
				$width = $directory[0] == 'thumbnails' ? 500 : 1400;

				$file = Storage::disk('public')->get($path);

				$manager = new ImageManager(new Driver());
				$image = $manager->read($file);

				$image->scale($width, null);

				return (string) $image->encode(new WebpEncoder(quality: 90));
			}

			return null;
		});

		if (!$cachedImage) {
			abort(404);
		}

		$headers = [
			'Content-Type' => 'image/webp',
			'Cache-Control' => 'public, max-age=2592000',
			'Expires' => now()->addMonth()->toHttpDate(),
			'Pragma' => 'public',
		];
		return response()->make($cachedImage, 200, $headers);
	}

}
