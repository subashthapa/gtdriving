<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InstructorInvitation extends Model
{
    protected $fillable = [
        'email',
        'token_hash',
        'role',
        'invited_by',
        'expires_at',
        'accepted_at',
        'accepted_user_id',
        'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected $appends = ['status'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedUser()
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function getStatusAttribute(): string
    {
        if ($this->accepted_at) {
            return 'accepted';
        }

        if ($this->revoked_at) {
            return 'revoked';
        }

        return $this->expires_at->isPast() ? 'expired' : 'pending';
    }
}
