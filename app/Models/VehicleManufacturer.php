<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class VehicleManufacturer extends Authenticatable
{
    protected $table = "vehicle_manufacturers";
        public $timestamps = false;
        
    protected $fillable = [
        'id',
        'name',
        'slug',
        'created_at',
        'updated_at',
        'is_active',
        'created_by',
        'updated_by'
    ];
 
 
    public function models() {
         return $this->hasMany(VehicleModel::class, 'make_id', 'id');
    }
    
}