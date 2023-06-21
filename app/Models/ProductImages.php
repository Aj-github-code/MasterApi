<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class ProductImages extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'product_images';
        
    }
    // protected $table = "product_images";
    protected $fillable = ['type','image_name_1','image_name_2','title','product_id','featured_image','priority','is_active','created','modified'];

    public $timestamps = false;
}
