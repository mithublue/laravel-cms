<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Term;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'featured_image_id',
        'status',
        'visibility',
        'published_at',
        'is_pinned',
        'allow_comments',
        'options',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'options' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(PostTranslation::class)->where('locale', app()->getLocale());
    }

    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function terms()
    {
        return $this->morphToMany(Term::class, 'termable', 'termables');
    }

    public function categories()
    {
        return $this->terms()->whereHas('taxonomy', function ($q) {
            $q->where('scope', 'post')->where('slug', 'categories');
        });
    }
}
