<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

use DB;

class SocietymgmtWing extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'wings';
    }
    
    protected $fillable = ['id','wing', 'email','contact_1', 'contact_2','is_active', 'created_at', 'created_by', 'modified_at', 'modified_by'];

    public $timestamps = false;
    
}
