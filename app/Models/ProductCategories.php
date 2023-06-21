<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImages;
use App\Models\products;
use App\Models\ProductProductCategory;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class ProductCategories extends Model
{
    use HasFactory;
    
      protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'product_categories';
       
    }
    // protected $table = "product_categories";
    protected  $primaryKey = 'id';
    protected $fillable = ['parent_id','category_name','description','slug','gst','hsn_code','image_name_1','image_name_2','full_banner','is_service','meta_title','meta_description','meta_keyword','is_active','created_at','modified_at','created_by','modified_by'];

    public $timestamps = false;
    
    public function assessmentDetails() {
         return $this->hasMany(AssessmentDetail::class, 'category_id', 'id');
    }
}
