<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExams extends Model
{
      protected $table = 'user_exams';

    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
         'campaign_code', 'exam_code', 'question_id', 'question_ans', 'user_ans', 'marks', 'is_active', 'created', 'created_by', 'modified', 'modified_by'
    ];
    
     public function userExamResult()
    {
        return $this->belongsTo(UserExamResult::class, 'exam_code', 'exam_code');
    }
}
