<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimInspectionImage extends Model
{
    use HasFactory;
    protected $table = "claim_inspection";
    protected $fillable = ['id','claim_id','image','category','damage','status'];
}
