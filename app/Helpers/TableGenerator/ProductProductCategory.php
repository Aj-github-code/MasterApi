<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class ProductProductCategory{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->integer('product_id', $autoIncrement = false, $unsigned = false);
            $table->integer('product_category_id',  $autoIncrement = false, $unsigned = false);
    
        });
    }
    
}