<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = 'companies';
        
    }
    protected $fillable = ['id','company_name', 'company_code','slug', 'logo', 'first_name','middle_name', 'surname', 'primary_email','secondary_email','contact_1','contact_2','meta_keyword','meta_title','meta_description','website','domain', 'sub_doamin', 'about_company','about_company_image','company_mission','company_mission_image','company_vision','company_address','company_vision_image','pan_no','gst_no','adhaar_no','is_active', 'created','modified', 'company_bank'];

    public $timestamps = false;
    
}
