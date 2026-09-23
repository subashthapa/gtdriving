<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'thumbnail',
        'added_by',
    ];
}
