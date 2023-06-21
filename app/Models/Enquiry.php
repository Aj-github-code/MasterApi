<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;

class Enquiry extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'enquiry';
        
    }
    protected $fillable = ['id','name', 'phone','email', 'enquiry_type', 'enquiry_code','address', 'remark', 'data','is_active', 'created','modified', 'created_by','modified_by'];

    public $timestamps = false;
    
}
