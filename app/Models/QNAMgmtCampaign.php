<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtCampaign extends Model
{
    
    use HasFactory;

    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'campaigns as campaigns';
    }
    
    protected $primaryKey = 'id';
    
    public $timestamps = false;
	
    protected $fillable = [
        'user_id', 'company_id', 'type', 'campaign_code',  'other_parameter', 'date', 'slug', 'created_at', 'created_by', 'modified_at', 'modified_by'
    ];

}
