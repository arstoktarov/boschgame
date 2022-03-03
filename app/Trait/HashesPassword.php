<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;

trait HashesPassword {

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = Hash::make($value);
    }
    
}