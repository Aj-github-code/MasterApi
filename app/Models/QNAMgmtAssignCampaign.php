<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtAssignCampaign extends Model
{
    
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'assign_campaign as assign_campaign';
    }
    

    protected $primaryKey = 'id';
    
    public $timestamps = false;
	
    protected $fillable = [
        'user_id', 'campaign_code', 'status', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];

}
