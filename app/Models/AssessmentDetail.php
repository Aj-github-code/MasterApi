<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDetail extends Model
{
    use HasFactory;
    protected $table = "assessment_details";
    protected $fillable = ['id','parent_id','is_product','assessment_id','category_id','batch_code','product_id','hsn_code','unit_price','gst','qty','amount_before_tax','amount_after_tax','remark','is_active','created_at','modified_at','created_by','modified_by', 'product_info'];

    public $timestamps = false;
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'id', 'assessment_id');
    }
    
    public function category()
    {
        return $this->belongsTo(ProductCategories::class, 'id', 'category_id');
    }
}
