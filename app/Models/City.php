<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Countries;
use App\Models\States;

class City extends Model
{
    use HasFactory;
    protected $table = "cities";
    protected $fillable = ['id','state_id','city_name', 'slug', 'short_name', 'country_id', 'type', 'population', 'population_class','is_active','created','modified'];

    //public $timestamps = false;
    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';
    
    public function country() {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }
    
    public function state() {
        return $this->hasOne(States::class, 'id', 'state_id');
    }
    
    public function scopeOfCountryWiseCities($query, $type)
    {
        if($type!='')
            return $query->where('country_id', $type);
        else
            return $query->where('is_active', 1);
    }
    
    public function scopeOfStateWiseCities($query, $type)
    {
        if($type!='')
            return $query->where('state_id', $type);
        else
            return $query->where('is_active', 1);
    }
    
    public function slugWiseData($slug){
        return $this->hasOne(City::class)->where('slug', $slug)->get(); 
    }
}
