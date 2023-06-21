<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class testride extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        
        if(Session::get('company_name')){
            
            $this->table = Session::get('company_name').'_testride as testride';
        } else {
            $this->table = 'testride';
        }
    }
    protected $fillable = ['id','name', 'email','mobile', 'address', 'city', 'state', 'pincode', 'data', 'is_active', 'created','modified','modified_by'];

    public $timestamps = false;
}
