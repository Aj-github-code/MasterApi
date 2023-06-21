<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;

class SocietymgmtFlatDetail extends Model
{
    use HasFactory;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'flat_details';
        
    }
    protected $fillable = ['id', 'user_unique_code', 'owner_name', 'email_id', 'contact_1', 'Contact_2', 'wing', 'floor_no', 'flat_no', 'rera_sqft', 'maintenance_amt', 'builder_company', 'maintenance_start_date', 'tenant_name', 'tenant_contact_no', 'registration_date', 'vehicle_number_1', 'vehicle_type_1', 'vehicle_number_2', 'vehicle_type_2', 'vehicle_number_3', 'vehicle_type_3', 'created_at', 'modified_at', 'created_by', 'modified_by', 'is_active'];

    public $timestamps = false;
}