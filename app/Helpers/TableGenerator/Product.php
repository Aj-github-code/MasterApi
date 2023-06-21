<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Product{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_category_id', $autoIncrement = false, $unsigned = false);
            $table->integer('product_type', $autoIncrement = false, $unsigned = false);
            $table->string('product', 255);
            $table->string('tally_name', 255)->nullable();
            $table->string('product_code', 255)->nullable();
            $table->string('slug', 255);
            $table->float('base_price', 10, 2);
            $table->string('base_uom', 255)->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keyword', 160)->nullable();
            $table->tinyInteger('is_pack')->default(0);
            $table->tinyInteger('show_on_website')->default(0);
            $table->tinyInteger('is_sale')->default(0);
            $table->tinyInteger('is_new')->default(0);
            $table->tinyInteger('is_gift')->default(0);
            $table->integer('is_featured', $autoIncrement = false, $unsigned = false)->default(0);
            $table->longText('banner_image');
            $table->longText('featured_image');
            $table->tinyInteger('overall_stock_mgmt')->default(0);
            $table->longText('data');
            $table->integer('priority', $autoIncrement = false, $unsigned = false)->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
        });
    }
    
}