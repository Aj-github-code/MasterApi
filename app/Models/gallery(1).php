<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class gallery extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        
        if(Session::get('company_name')){
            
            $this->table = Session::get('company_name').'_gallery as gallery';
        } else {
            $this->table = 'gallery';
        }
    }
    protected $fillable = ['id','title', 'images', 'is_active', 'created_at','created_by','modified_at','modified_by'];

    public $timestamps = false;
}
