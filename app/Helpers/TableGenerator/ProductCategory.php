<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class ProductCategory{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id', $autoIncrement = false, $unsigned = false)->nullable();
            $table->string('category_name', 255);
            $table->tinyInteger('is_service')->default(0);
            $table->longText('description')->nullable();
            $table->string('slug', 255);
            $table->string('link', 255)->nullable();
            $table->string('gst', 255)->nullable();
            $table->string('hsn_code', 255);
            $table->string('image_name_1', 255);
            $table->string('image_name_2', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('full_banner')->default(0);
            $table->string('meta_title', 160);
            $table->string('meta_description', 255);
            $table->string('meta_keyword', 160);
            
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
        });
    }
    
}