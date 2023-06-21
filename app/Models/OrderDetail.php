<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
// session(['company'=>'company_products']);
class OrderDetail extends Model
{
    
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'order_details as order_details';
    }

    
    protected  $primaryKey = 'id';
    protected $fillable = ['order_id', 'product_details', 'price', 'order_detail_code','qty','return_qty', 'is_active', 'created_at','modified_at','created_by','modified_by'];
    public $timestamps = false;
    
}