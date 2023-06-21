<?php
namespace App\Helpers\Tables\DeliveryMgmt;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class CashBookLog{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'cash_book_log'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('cash_book_id', $autoIncrement = false, $unsigned = false);
                $table->string('order_code', 50);
                $table->string('name', 50)->nullable();
                $table->string('type', 20);
                $table->string('payment_mode', 50);
                $table->float('amt', $precision = 10, $scale = 2);
                
                
                $table->dateTime('created_at');    
                $table->dateTime('modified_at')->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'cash_book_log'){
        Schema::dropIfExists($tableName);
    }
}