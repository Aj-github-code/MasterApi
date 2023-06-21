<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;


class QNAMgmtCampaignAnswer extends Model
{

    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'campaign_answers as campaign_answers';
    }
    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
        'question_id', 'answers', 'is_ans', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];
    
    public function campaign_question()
    {
        return $this->belongsTo(QNAMgmtCampaignQuestion::class, 'id', 'question_id');
    }
}
