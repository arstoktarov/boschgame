<?php

namespace App\Models;

use App\Casts\Image;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['category_id', 'title'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
       // 'image' => Image::class,
    ];

    public function answers() {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
