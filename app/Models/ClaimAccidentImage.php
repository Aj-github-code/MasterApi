<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAccidentImage extends Model
{
    use HasFactory;
    protected $table = "claim_accident";
    protected $fillable = ['id','claim_id','image','category','status'];
}
