<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CampaignAnswer extends Model
{
    protected $table = 'campaign_answers';

    protected $primaryKey = 'id';
	
	public $timestamps = false;
	
    protected $fillable = [
        'question_id', 'answers', 'is_ans', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];
    
    public function campaign_question()
    {
        return $this->belongsTo(CampaignQuestion::class, 'id', 'question_id');
    }
}
