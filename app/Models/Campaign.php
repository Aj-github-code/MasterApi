<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $primaryKey = 'id';
    
    public $timestamps = false;
	
    protected $fillable = [
        'user_id', 'company_id', 'role_id', 'type', 'campaign_code', 'total_question','total_marks',  'passing_marks', 'passing_percent','date', 'slug', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];

}
