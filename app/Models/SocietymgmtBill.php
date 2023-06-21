<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;
use App\Models\SocietymgmtBillDetail;

class SocietymgmtBill extends Model
{
    use HasFactory;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'bills';
        
    }
    protected $fillable = ['id', 'flat_detail_id', 'flat_details', 'invoice_no', 'invoice_date', 'billing_month', 'no_of_days', 'bill_from', 'bill_to', 'amt_before_tax', 'other_charges', 'discount', 'tax', 'amt_after_tax', 'status', 'is_active', 'created_by', 'created_at', 'modified_by', 'modified_at'];

    public $timestamps = false;
    
    public function billDetails(){
        return $this->hasMany(SocietymgmtBillDetail::class, 'invoice_no', 'invoice_no');
    }
    
    public static function invoiceCount(){
        return DB::select('Select count(id) from '.$this->table.' where invoice_no LIKE "'.Helper::get_fiscal_year().'/%"')->get();
    }
}