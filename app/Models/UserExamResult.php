<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExamResult extends Model
{
      protected $table = 'user_exam_results';

    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
         'campaign_code', 'user_id', 'exam_code', 'marks', 'status','is_active', 'created', 'created_by', 'modified', 'modified_by'
    ];
    
     public function userExam()
    {
        return $this->hasMany(UserExams::class, 'exam_code', 'exam_code');
    }
    public function getUserExamDetail($campaign_code){
        return $this->hasOne(Campaign::class)->where('campaign_code', $campaign_code)->get();
    }
}
