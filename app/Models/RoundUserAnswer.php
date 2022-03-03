<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoundUserAnswer extends Model
{

    protected $hidden = ['created_at', 'updated_at', 'id', 'game_round_question_id', 'answer_id'];

    protected $fillable = ['user_id', 'round_id', 'question_id', 'answer_id'];

    protected $casts = [
        'user_id' => 'integer',
        'round_id' => 'integer',
        'question_id' => 'integer',
        'answer_id' => 'integer'
    ];


    public function answer() {
        return $this->belongsTo(Answer::class);
    }
}
