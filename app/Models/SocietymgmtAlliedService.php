<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;

class SocietymgmtAlliedService extends Model
{
    use HasFactory;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'allied_services';
        
    }
    protected $fillable = ['id', 'category', 'services', 'slug', 'cost', 'frequency', 'product_type', 'is_active', 'created_by', 'created_at', 'modified_by', 'modified_at'];

    public $timestamps = false;
}