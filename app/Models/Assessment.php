<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;
    protected $table = "assessment";
    protected $fillable = ['id','claim_id','assessment_code','part_charges','labour_charges','paint_charges','other_charges','grand_total','status','form_step','is_active','created_at','modified_at','created_by','modified_by'];

    public $timestamps = false;
    
    public function assessmentDetails()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id', 'id');
    }
}
