<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignCampaign extends Model
{
    
        use HasFactory;
    protected $table = 'assign_campaign';

    protected $primaryKey = 'id';
    
    public $timestamps = false;
	
    protected $fillable = [
        'user_id', 'campaign_code', 'status', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];

}
