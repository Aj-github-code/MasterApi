<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImages;
use App\Models\ProductCategories;
use App\Models\products;
use App\Helpers\Helper as Helper;

class ProductProductCategory extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'product_product_categories';
        
    }
    protected $fillable = ['product_id','product_category_id'];

    public $timestamps = false;
}
