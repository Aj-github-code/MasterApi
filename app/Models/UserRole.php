<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


use App\Helpers\Helper as Helper;

class UserRole extends Authenticatable
{
 
    // protected $table = "user_roles";
    
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'user_roles as user_roles';
    }
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'role_id',
        'account_type',
        'is_active',
        'created_at',
        'modified_at'
    ];
}
