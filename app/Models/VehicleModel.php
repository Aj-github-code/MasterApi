<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class VehicleModel extends Authenticatable
{
    
    protected $table = "vehicle_model";
        public $timestamps = false;
        
    protected $fillable = [
        'id',
        'make_id',
        'vehicle_type_id',
        'model_code',
        'name',
        'oem',
        'oem_sub_type',
        'model_master_id',
        'is_three_wheeler',
        
        'created_at',
        'updated_at',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
      
    public function manufacturer() {
       return $this->belongsTo(VehicleManufacturer::class, 'id', 'make_id');
    }
    
    
    public function vehicleType() {
         return $this->belongsTo(VehicleType::class, 'id', 'vehicle_type_id');
    }
    
    public function variant() {
         return $this->hasMany(VehicleVariant::class, 'model_code', 'model_code');
    }


}
