<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

use DB;

class SocietymgmtMonthlyOutstanding extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'monthly_outstandings';
    }
    // protected $table = "setup";
    protected $fillable = ['id','type', 'user_id','flat_detail_id', 'month','amount', 'created_at'];

    public $timestamps = false;
    
}
