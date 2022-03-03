<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlacklist extends Model
{
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
