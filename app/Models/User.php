<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

use App\Helpers\Helper as Helper;
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims() 
    {
        return [];
    }

    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'users as users';
    }

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     
    protected $primaryKey = 'id';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'name',
        'org_type',
        'organisation',
        'profile_image',
        'email',
        'mobile',
        'password',
        'address',
        'city',
        'state',
        'district',
        'pincode',
        'remember_token',
        'lat',
        'lng',
        'status',
        'is_active',
        'lock_out',
        'lock_out_time',
        'failed_login',
        'created_at',
        'created_by',
        'modified_at',
        'modified_by'
    ];

 
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles() {
        //  return $this->hasMany(UserRole::class, 'user_id', 'id')
       return User::join(Helper::getCompany().'user_roles as ur', 'ur.user_id', '=', 'users.id', 'left')
        ->join(Helper::getCompany().'roles as r', 'r.id', '=', 'role_id')
        ->select('r.*')
        ->orderBy('r.role_name', 'asc');
     }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
