<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtUserReportingManager extends Model
{
    
    
    
      use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'user_reporting_manager as user_reporting_manager';
    }
    
    public $timestamps = false;
    protected $fillable = [
        'id',
        'user_id',
        'rm_id',
        'is_active',
        'created_at',
        'created_by',
        'modified_at'
    ];
}
