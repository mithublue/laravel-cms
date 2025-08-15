<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Term;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'author_id',
        'featured_image_id',
        'status',
        'visibility',
        'published_at',
        'is_featured',
        'options',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'options' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations()
    {
        return $this->hasMany(NewsTranslation::class, 'news_id');
    }

    public function translation()
    {
        return $this->hasOne(NewsTranslation::class, 'news_id')->where('locale', app()->getLocale());
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
            $q->where('scope', 'news')->where('slug', 'categories');
        });
    }
}
