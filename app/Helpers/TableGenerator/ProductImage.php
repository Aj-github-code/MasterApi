<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class ProductImage{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50);
            $table->integer('product_id', $autoIncrement = false, $unsigned = false);
            $table->string('title', 255);
            $table->string('image_name_1', 255)->nullable();
            $table->string('image_name_2', 255)->nullable();
            $table->tinyInteger('featured_image')->default(0);
            $table->integer('priority', $autoIncrement = false, $unsigned = false)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
    }
    
}