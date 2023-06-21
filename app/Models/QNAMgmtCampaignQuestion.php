<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtCampaignQuestion extends Model
{

    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'campaign_questions as campaign_questions';
    }
    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
        'question_type', 'campaign_code', 'question_code', 'question', 'choice_type', 'allow_single_answer','weightage', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];
    
     public function campaign_answer()
    {
        return $this->hasMany(QNAMgmtCampaignAnswer::class, 'question_id', 'id');
    }
}
