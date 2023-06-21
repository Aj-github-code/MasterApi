<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
// session(['company'=>'company_products']);
class Order extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'orders as orders';
    }

    
    protected  $primaryKey = 'id';
    protected $fillable = ['id','order_code','invoice_no', 'invoice', 'total_amt','received_amt', 'pending_amt', 'pickup_details', 'user_details','order_date','delivery_date', 'payment_mode', 'is_active', 'created_at','modified_at','created_by','modified_by'];

    public $timestamps = false;
    
    public function orderDetails(){
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
    
 
}