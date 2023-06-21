<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;
// session(['company'=>'company_products']);
class CashBook extends Model
{

    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'cash_book as cash_book';
    }
    protected  $primaryKey = 'id';
    
    protected $fillable = ['id','user_id', 'opening_balance', 'closing_balance', 'date', 'modified_at'];
    
    
    function cashBookLog()
    {
        return $this->hasMany(CashBookLog::class)->where('cash_book_log.payment_mode', 'LIKE', 'cash');
    }
    
    public $timestamps = false;
}