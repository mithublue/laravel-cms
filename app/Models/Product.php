<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Term;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'type',
        'sku',
        'price',
        'sale_price',
        'currency',
        'stock_qty',
        'manage_stock',
        'stock_status',
        'backorder',
        'featured_image_id',
        'weight',
        'dimensions',
        'status',
        'visibility',
        'published_at',
        'options',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'manage_stock' => 'boolean',
        'backorder' => 'boolean',
        'dimensions' => 'array',
        'options' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(ProductTranslation::class)->where('locale', app()->getLocale());
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
            $q->where('scope', 'product')->where('slug', 'categories');
        });
    }
}
