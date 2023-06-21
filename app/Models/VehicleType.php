<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class VehicleType extends Authenticatable
{
    
    protected $table = "vehicle_types";
        public $timestamps = false;
        
    protected $fillable = [
        'id',
        'vehicle_type',
        'slug',
        'created_at',
        'updated_at',
        'is_active',
        'created_by',
        'updated_by'
    ];
 
    public function vehicleModel() {
         return $this->hasMany(VehicleModel::class, 'vehicle_type_id', 'id');
    }

}
