<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    use HasFactory;
    protected $table = "states";
    protected $fillable = ['id','country_id','state_name', 'slug','gst_state_code','is_active','created','modified'];

    //public $timestamps = false;
    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';
    
    public function country() {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }
    
    public function scopeOfCountryWiseStates($query, $type)
    {
        if($type!='')
            return $query->where('country_id', $type);
        else
            return $query->where('is_active', 1);
    }
    
    public function slugWiseData($slug){
        return $this->hasOne(States::class)->where('slug', $slug)->get(); 
    }
    
    public function cities() {
        return $this->hasMany(City::class, 'id', 'state_id');
    }
}
