<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::query()->latest();
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%")
                  ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        if ($mime = $request->string('mime')->toString()) {
            $query->where('mime_type', 'like', $mime.'%');
        }

        $perPage = min(max((int) $request->get('per_page', 24), 1), 100);
        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Media $m) {
            return [
                'id' => $m->id,
                'url' => $m->url(),
                'original_name' => $m->original_name,
                'filename' => $m->filename,
                'mime_type' => $m->mime_type,
                'size' => $m->size,
                'width' => $m->width,
                'height' => $m->height,
                'created_at' => $m->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'data' => $items,
            'links' => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
