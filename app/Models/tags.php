<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tags extends Model
{
    use HasFactory;
    protected $table = "tags";
    protected $fillable = ['id','name','slug', 'is_active', 'created_at', 'created_by', 'modified_at', 'modified_by'];
    public $timestamps = false;
}
