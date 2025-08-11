<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|image|max:5120', // images up to 5MB
        ]);

        $media = MediaService::storeFromUpload($request->file('file'), $request->user()->id, 'uploads/'.date('Y/m'));

        return response()->json([
            'id' => $media->id,
            'url' => $media->url(),
            'name' => $media->original_name ?? $media->filename ?? null,
            'mime' => $media->mime_type ?? null,
            'size' => $media->size ?? null,
            'width' => $media->width ?? null,
            'height' => $media->height ?? null,
        ]);
    }
}
