<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vehicleImage extends Model
{
    use HasFactory;
      
    protected $table = "vehicle_images";
        public $timestamps = false;
    protected $fillable = [
        'id',
        'vehicle_id',
        'images',
        'type',
        'priority',
        'is_active',
        'created_at',
        'modified_at',
        'created_by',
        'modified_by'
    ];
}
