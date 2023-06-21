<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class Testimonials extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        
        $this->table = Helper::getCompany().'testimonials';
        // print_r($this->table);
    }
    protected $fillable = ['id','title', 'image','description', 'priority', 'is_active', 'created','created_by','modified','modified_by'];

    public $timestamps = false;
    
}
