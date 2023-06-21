<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AssignClaim extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "assign_claim";
    protected $fillable = [
        'claim_id',
        'user_id',
        'role_id',
        'assessment_id',
        'is_active',
    ];
}
