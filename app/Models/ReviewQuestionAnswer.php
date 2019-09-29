<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewQuestionAnswer extends Model
{
    protected $guarded = ['id'];
    protected $table = 'review_question_answer';

    public function answer()
    {
        return $this->belongsTo(RateAnswer::class, 'rate_answer_id');
    }
}