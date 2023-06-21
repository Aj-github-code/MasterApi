<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtUserExams extends Model
{
    
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'user_exams as user_exams';
    }

    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
         'campaign_code', 'exam_code', 'question_id', 'question_ans', 'user_ans', 'marks', 'is_active', 'created', 'created_by', 'modified', 'modified_by'
    ];
    
     public function userExamResult()
    {
        return $this->belongsTo(QNAMgmtUserExamResult::class, 'exam_code', 'exam_code');
    }
}
