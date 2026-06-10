<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Store an uploaded image directly under public/uploads and return its URL.
     *
     * Used by the TipTap editor (image button) and the cover-image picker.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
        ]);

        $path = $validated['file']->store(now()->format('Y/m'), 'uploads');
        $publicPath = 'uploads/'.$path;

        return response()->json([
            'path' => $publicPath,
            'url' => asset($publicPath),
        ], 201);
    }
}
