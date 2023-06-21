<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtUserExamResult extends Model
{
    
    use HasFactory;
    protected $table;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'user_exam_results as user_exam_results';
    }
    
    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
         'campaign_code', 'user_id', 'exam_code', 'marks', 'status','is_active', 'created', 'created_by', 'modified', 'modified_by'
    ];
    
     public function userExam()
    {
        return $this->hasMany(QNAMgmtUserExams::class, 'exam_code', 'exam_code');
    }
    public function getUserExamDetail($campaign_code){
        return $this->hasOne(QNAMgmtCampaign::class)->where('campaign_code', $campaign_code)->get();
    }
}
