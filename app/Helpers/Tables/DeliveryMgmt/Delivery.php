<?php
namespace App\Helpers\Tables\DeliveryMgmt;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Delivery{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'delivery'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('delivery_code', 255);
            $table->integer('delivery_boy_id', $autoIncrement = false, $unsigned = false);
            $table->integer('order_id', $autoIncrement = false, $unsigned = false);
            $table->dateTime('delivery_date')->nullable();
            
            $table->tinyInteger('status')->default(1);
            $table->longtext('slug')->nullable();
            
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by', $autoIncrement = false, $unsigned = false)->nullable();
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        }
    }
    
    public static function delete($tableName = 'delivery'){
        Schema::dropIfExists($tableName);
    }
}