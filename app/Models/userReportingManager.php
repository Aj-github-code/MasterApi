<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

use App\Helpers\Helper as Helper;

class userReportingManager extends Model
{
    use HasFactory;
    protected $table = "user_reporting_manager";
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
