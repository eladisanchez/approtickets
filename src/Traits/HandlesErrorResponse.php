<?php

namespace ApproTickets\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

trait HandlesErrorResponse
{
    public function handleErrorResponse($message): JsonResponse|RedirectResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['error' => $message]);
        }
        return redirect()->back()->with('error', $message);
    }
}
