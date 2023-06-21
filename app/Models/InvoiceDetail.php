<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImages;
use App\Models\ProductCategories;
use App\Models\ProductProductCategory;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class InvoiceDetail extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'invoice_details';
    }
    
    protected  $primaryKey = 'id';
    protected $fillable = ['product_id','invoice_id', 'unique_code', 'hsn_code','qty', 'base_price','gst','description','amount_after_tax','amount_before_tax','is_active', 'created_at','modified_at','created_by','modified_by'];

    public $timestamps = false;
    
    public function invoice() {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
    
}
?>