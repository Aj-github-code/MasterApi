<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;
use App\Models\SocietymgmtBill;

class SocietymgmtBillDetail extends Model
{
    use HasFactory;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'bill_details';
        
    }
    protected $fillable = ['id', 'invoice_no', 'product_id', 'product_details', 'unit_price', 'qty', 'amt', 'tax', 'amt_after_tax', 'is_active', 'created_at', 'created_by', 'modified_at', 'modified_by'];

    public $timestamps = false;
    
    public function invoice() {
        return $this->hasOne(SocietymgmtBill::class, 'invoice_no', 'invoice_no');
    }
}