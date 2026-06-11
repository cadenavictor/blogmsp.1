<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * POST /api/media — upload an image (multipart "file") to public/uploads.
     *
     * Stores in a physical directory (no storage:link symlink, which is
     * problematic on shared hosting). Returns the public URL to use as
     * cover_image_url when creating a post.
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
