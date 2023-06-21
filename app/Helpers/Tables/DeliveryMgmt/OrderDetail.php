<?php
namespace App\Helpers\Tables\DeliveryMgmt;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class OrderDetail{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'order_details'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id', $autoIncrement = false, $unsigned = false);
                $table->longtext('product_details')->nullable();
                $table->string('order_detail_code', 255)->nullable(); 
                $table->float('price', $precision = 10, $scale = 2);
                $table->integer('qty', $autoIncrement = false, $unsigned = false);
                $table->integer('return_qty', $autoIncrement = false, $unsigned = false);
                
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->integer('created_by', $autoIncrement = false, $unsigned = false)->nullable();
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'order_details'){
        Schema::dropIfExists($tableName);
    }
}