<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    use HasFactory;
    protected $table = "countries";
    protected $fillable = ['id', 'name', 'short_name', 'slug', 'currrency', 'currency_code', 'is_active','created','modified'];

    //public $timestamps = false;
    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';
    
    public function state() {
         return $this->hasMany(States::class, 'country_id', 'id');
    }
    
    public function countrywisestate($countryId) {
         return $this->hasMany(States::class, 'country_id', 'id')
            ->where('country_id', $countryId)
            ->select('states.*');
    }
    
    public function cities() {
        return $this->hasMany(City::class, 'id', 'country_id');
    }
}
