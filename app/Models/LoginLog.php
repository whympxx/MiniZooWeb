<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'login_time', 'ip_address'];

    protected function casts(): array
    {
        return ['login_time' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
