<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImages;
use App\Models\ProductCategories;
use App\Models\ProductProductCategory;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
use DB;
class Invoice extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'invoices';
    }
    
    protected  $primaryKey = 'id';
    protected $fillable = ['customer_id','customer_name', 'fiscal_yr', 'invoice_no', 'date', 'email','contact_no', 'gst_no', 'address', 'user_details', 'status','amount_after_tax','amount_before_tax','adjustment','grand_total','discount','is_active', 'created_at','modified_at','created_by','modified_by'];

    public $timestamps = false;
    
    public function invoiceDetails(){
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'id');
    }
    
    public static function invoiceCount(){
        return DB::select('Select count(id) from '.$this->table.' where invoice_no LIKE "INV/'.Helper::get_fiscal_year().'/%"')->get();
    }
    
}
?>