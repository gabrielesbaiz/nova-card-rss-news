<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourcesController
{
    /**
     * The catalogue grouped by category, for the source picker.
     */
    public function index(Request $request, SourceRepository $sources): JsonResponse
    {
        /** @var array<int, string> $only */
        $only = array_values(array_filter((array) $request->input('categories', [])));

        return response()->json(['categories' => $sources->grouped($only)]);
    }
}
