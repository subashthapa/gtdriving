<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'package_name',
        'subtitle',
        'price',
        'image',
        'thumbnail',
        'status',
        'added_by'
    ];
    //
}
