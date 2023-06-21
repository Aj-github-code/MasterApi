<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEstimation extends Model
{
    use HasFactory;
    protected $table = "user_estimations";
    protected $fillable = ['id','user_id','role_id','assessment_id','last_estimation','created','modified','created_by','modified_by'];

    public $timestamps = false;
}
