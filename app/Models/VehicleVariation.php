<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;


class VehicleVariation extends Authenticatable
{
    
    protected $table = "vehicle_variations";
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'vehicle_type_id',
        'name',
        'value',
        'remark',
        'datatype',
        'is_active',
        'created_at',
        'modified_at',
        'created_by',
        'modified_by'
    ];
    
       
    public function vehicleType() {
       return $this->belongsTo(VehicleType::class, 'id', 'vehicle_type_id');
    }
 


}