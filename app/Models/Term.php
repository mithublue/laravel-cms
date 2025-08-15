<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'order',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    public function parent()
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    public function translations()
    {
        return $this->hasMany(TermTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(TermTranslation::class)->where('locale', app()->getLocale());
    }

    // Polymorphic relations to content types (inverse side)
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'termable');
    }

    public function news()
    {
        return $this->morphedByMany(News::class, 'termable');
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'termable');
    }
}
