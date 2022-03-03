<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }
}
