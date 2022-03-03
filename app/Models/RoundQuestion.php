<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoundQuestion extends Model
{

    protected $hidden = ['created_at', 'updated_at', 'game_round_id', 'question_id'];

    protected $fillable = ['round_id', 'question_id'];


    public function gameRound() {
        return $this->belongsTo(Round::class, 'round_id');
    }

    public function userAnswers() {
        return $this->hasMany(RoundUserAnswer::class, 'round_question_id');
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }
}
