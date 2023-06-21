<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
// session(['company'=>'company_products']);
class CashBookLog extends Model
{
    
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'cash_book_log as cash_book_log';
    }
    
    protected  $primaryKey = 'id';
    
    protected $fillable = ['id','cash_book_id', 'order_code', 'name', 'type', 'payment_mode', 'amt', 'created_at', 'modified_at'];
    
    public function CashBook(){
        return $this->hasOne(CashBook::class, 'id', 'cash_book_id');
    }
    public $timestamps = false;
}

