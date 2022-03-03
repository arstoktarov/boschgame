<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail($country_id)
 */
class Country extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function cities() {
        return $this->hasMany(City::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
