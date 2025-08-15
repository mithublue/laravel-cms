<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_id',
        'locale',
        'name',
        'slug',
        'description',
        'seo',
    ];

    protected $casts = [
        'seo' => 'array',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
