<?php

namespace App\Models;

use App\Casts\Image;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    const DEFAULT_RANDOM_COUNT = 3;
    const IMAGE_PATH = 'cats';

    protected $fillable = ['title'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'image' => Image::class
    ];

    public function questions() {
        return $this->hasMany(Question::class, 'category_id');
    }
}
