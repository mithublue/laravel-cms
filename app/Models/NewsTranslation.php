<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'seo',
    ];

    protected $casts = [
        'seo' => 'array',
    ];

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
