<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['title', 'question_id'];

    protected $hidden = ['created_at', 'updated_at', 'question_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function question() {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
