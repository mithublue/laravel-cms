<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'disk',
        'directory',
        'filename',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'caption',
        'focal_point',
        'variants',
        'hash',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'focal_point' => 'array',
        'variants' => 'array',
    ];

    public function getPathAttribute(): string
    {
        return trim(implode('/', array_filter([$this->directory, $this->filename])), '/');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
