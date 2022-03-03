<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameUser extends Model
{
    const FIRST_PLAYER = 1;
    const SECOND_PLAYER = 2;

    protected $table = 'game_users';

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['user_id', 'game_id', 'player_number'];

}
