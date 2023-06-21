<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImages;
use App\Models\ProductCategories;
use App\Models\ProductProductCategory;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class product extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'products';
        
    }

    use HasFactory;
    // var temp = $this->setTable();
    // print_r()
 
    
    
        protected  $primaryKey = 'id';
    protected $fillable = ['product_category_id','product_master_id', 'base_uom', 'product_type','product', 'tally_name', 'product_code','slug','base_price','gst','description','meta_title','meta_description','is_active','meta_keyword','banner_image', 'featured_image', 'is_pack', 'is_sale', 'is_new', 'is_gift', 'is_featured', 'show_on_website', 'overall_stock_mgmt', 'created','modified','created_by','modified_by'];

    public $timestamps = false;
    
    public function hasOneProductImage(){
        //return $this->hasOne(Pro::class, 'id', );
    }
    
    public function productImages(){
        return $this->hasMany(ProductImages::class, 'product_id', 'id');
    }
    
   
}
