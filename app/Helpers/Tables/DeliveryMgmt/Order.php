<?php
namespace App\Helpers\Tables\DeliveryMgmt;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Order{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'orders'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('order_code', 100);
                $table->string('invoice', 255);
                $table->string('invoice_no', 50);
                $table->float('total_amt', $precision = 10, $scale = 2);
                $table->float('received_amt', $precision = 10, $scale = 2);
                $table->float('pending_amt', $precision = 10, $scale = 2);
                $table->longtext('pickup_details')->nullable();
                $table->longtext('user_details')->nullable();
                $table->dateTime('order_date')->nullable();
                $table->dateTime('delivery_date')->nullable();
                $table->string('payment_mode', 50);
                
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->integer('created_by', $autoIncrement = false, $unsigned = false)->nullable();
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'orders'){
        Schema::dropIfExists($tableName);
    }
}