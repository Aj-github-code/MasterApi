<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignQuestion extends Model
{
      protected $table = 'campaign_questions';

    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
        'question_type', 'campaign_code', 'question_code', 'question', 'choice_type', 'allow_single_answer','weightage', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];
    
     public function campaign_answer()
    {
        return $this->hasMany(CampaignAnswer::class, 'question_id', 'id');
    }
}
