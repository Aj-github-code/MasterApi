<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;

class Blogs extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'blogs';
        
    }
    protected $fillable = ['id','title','image','banner_image','slug','description','is_active','created_at','modified_at','created_by','modified_by'];

}