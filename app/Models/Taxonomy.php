<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'scope',
        'hierarchical',
        'multiple',
        'options',
    ];

    protected $casts = [
        'hierarchical' => 'boolean',
        'multiple' => 'boolean',
        'options' => 'array',
    ];

    public function terms()
    {
        return $this->hasMany(Term::class);
    }
}
