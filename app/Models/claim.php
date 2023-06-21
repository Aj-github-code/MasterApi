<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class claim extends Model
{
    use HasFactory;
    protected $table = "claim";
    protected $fillable = ['id','claim_code','policy_id','insurance_company','allow_additional_claim','insured_name','policy_start_date','policy_end_date','less_salvage_adjusted','vehicle_idv_value','insured_mobile_no','insured_dob','insured_email_id','insured_nominee_name','hypothecation_lease','insured_address1','insured_address2','insured_address3','state','city','pincode','vehicle_registration_no','vehicle_registration_date','vehicle_product_type','cc_hp','less_excess','voluntary_deductible','occupation','vehicle_details','vehicle_make','vehicle_model','variant','vehicle_engine_no','vehicle_chassis_no','vehicle_odometer_reading','vehicle_fuel_type','anti_theft_device_status','vehicle_mfg_year','vehicle_seating_capacity','vehicle_color','vehicle_type_of_body','nil_depreciation','is_breakin','engine_protect','emi_protect','tyre_cover','rti','ncb_retention','key_replacement','pan_no','aadhaar_card_no','date_of_accident','time_of_accident','accident_state','accident_city','place_of_accident','damage','initial_estimate','vehicle_reported_date','vehicle_plying_from','vehicle_plying_to','vehicle_speed','nol_type','vehicle_used_for','towing','accident_reported_to_police','punchnama_carried_out','injury_to_driver_occupant','third_party_involved_in_accident','particulars_of_third_party_injury_loss','cause_and_nature_of_accident', 'driver_details', 'driver_name','driver_mobile_no','driving_license_type','driving_license_number','license_issuing_authority','driver_license_valid_till','driver_type','driver_address','driver_under_influence_of_alcohol','driver_involved_in_any_other_accident_in_last_two_years','updated_at','updated_by','is_active','status', 'form_step', 'workshop_id'];

}
