<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'slug',
        'short_description',
        'description',
        'seo',
    ];

    protected $casts = [
        'seo' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
