<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

use App\Helpers\Helper as Helper;

class Roles extends Model
{
    use HasFactory;
    protected $table;
        public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'roles as roles';
        
    }
  
    protected $fillable = ['role_name','role_code','slug','is_active','created_at','modified_at'];
    
    public $timestamps = false;
    
    public function roleUsers(){
        return $this->hasOne(UserRole::class, 'role_id', 'id');
    }
}
