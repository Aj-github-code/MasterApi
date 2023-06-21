<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper as Helper;

class BlogsCategories extends Model
{
    use HasFactory;
    //protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'blogs_categories';
        
    }
    protected $fillable = ['blog_id','category_id'];

    public $timestamps = false;
}