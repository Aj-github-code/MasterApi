<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
// session(['company'=>'company_products']);
class Delivery extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'delivery as delivery';
    }
    
 
    protected  $primaryKey = 'id';
    protected $fillable = ['id','delivery_code', 'order_id', 'delivery_boy_id', 'status', 'remark', 'delivery_date', 'created_at', 'created_by', 'modified_at', 'modified_by'];
    public $timestamps = false;
    
    
    public function orders(){
        return $this->hasMany(Order::class,  'id', 'order_id')->with('orderDetails');
    }
    
    
}