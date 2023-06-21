<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

use App\Helpers\Helper as Helper;

class Sliderdetail extends Model
{
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'slider_details';
    }
    // protected $table = "slider_details";
    protected $fillable = ['id','slider_id', 'filter_text','type', 'title_1','title_2','short_description','image','priority','link', 'is_active', 'created','modified'];

    public $timestamps = false;
    
    public function slider(){
        return $this->belongsTo(Slider::class, 'id', 'slider_id');
    }
}
