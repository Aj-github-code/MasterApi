<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class VehiclePart extends Authenticatable
{
    
    protected $table = "vehicle_parts";
    
    protected $fillable = [
        'id',
        'type',
        'name',
        'created_at',
        'modified_at',
        'is_active',
        'created_by',
        'modified_by'
    ];
 

}
