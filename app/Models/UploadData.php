<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadData extends Model
{
    use HasFactory;
    protected $table = "upload_file";
    protected $fillable = ['id','category','type', 'name', 'is_active', 'created','created_by','modified','modified_by'];

    public $timestamps = false;
}
