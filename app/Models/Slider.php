<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

use App\Models\Sliderdetail;

class Slider extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        
        $this->table = Helper::getCompany().'sliders';
        
    }
    // protected $table = "sliders";
    protected $fillable = ['id','name', 'js','slider_code', 'css', 'is_active', 'created','modified'];

    public $timestamps = false;
    
    public function sliderDetails(){
        return $this->hasMany(Sliderdetail::class, 'slider_id', 'id');
    }
}
