<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'instructor',
        'approved_by',
        'instructions',
        'amount',
        'payment_method',
        'payment_status',
        'paid_at',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instructorUser()
    {
        return $this->belongsTo(User::class, 'instructor');
    }

}
