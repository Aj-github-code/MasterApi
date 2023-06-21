<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class VehicleVariant extends Authenticatable
{
    
    protected $table = "vehicle_variant";
    
    protected $fillable = [
        'id',
        'variant_code',
        'model_code',
        'name',
        'cc',
        'gvw',
        'seating_capacity',
        'fuel_type',
         'no_of_wheels',
        'is_bifuel_facfitted',
        'is_anti_theft_facfitted',
        'created_at',
        'updated_at',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
       
    public function model() {
       return $this->belongsTo(VehicleModel::class, 'model_code', 'model_code');
    }
 


}
