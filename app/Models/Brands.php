<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

use DB;

class Brands extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'manufacturing_brands';
    }
    // protected $table = "setup";
    protected $fillable = ['id','brand_name', 'description','logo', 'is_active','created_at','modified_at', 'created_by', 'modified_by'];

    public $timestamps = false;
    
}
