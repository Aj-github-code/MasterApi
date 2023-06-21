<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class BlogsCategories{
    public function __construct($params = array())
    {
        
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->integer('blog_id', $autoIncrement = false, $unsigned = false);
            $table->integer('category_id');
        });
    }
    
}