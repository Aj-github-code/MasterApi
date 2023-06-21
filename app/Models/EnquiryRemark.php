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
        $this->table = Helper::getCompany().'enquiry_remark';
        
    }
    protected $fillable = ['id', 'enquiry_id', 'remark', 'is_active', 'created_at','modified_at', 'created_by','modified_by'];

    public $timestamps = false;
    
}
