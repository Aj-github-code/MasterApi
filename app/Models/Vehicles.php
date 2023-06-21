<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class Vehicles extends Authenticatable
{
    protected $table = "vehicles";
        public $timestamps = false;
    protected $fillable = [
        'id',
        'vehicle_type_id',
        'system_code',
        'make_id',
        'model_id',
        'transmission',
        'fuel_type',
        'base_price',
        'vehicle_status',
        'used_vehicle',
          'specification',
        'features',
        'images',
        'featured_image',
        'created_at',
        'modified_at',
        'created_by',
        'modified_by'
    ];
 
 
    public function models() {
         return $this->hasMany(VehicleModel::class, 'id','model_id');
    }
    
     public function make() {
         return $this->hasMany(VehicleManufacturer::class, 'id', 'make_id');
    }
    
    public function types() {
        return $this->hasMany(VehicleType::class, 'id', 'vehicle_type_id');
    }
}