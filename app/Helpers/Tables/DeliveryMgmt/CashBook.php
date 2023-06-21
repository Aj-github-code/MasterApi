<?php
namespace App\Helpers\Tables\DeliveryMgmt;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class CashBook{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'cash_book'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id', $autoIncrement = false, $unsigned = false);
                $table->float('opening_balance', $precision = 10, $scale = 2);
                $table->float('closing_balance', $precision = 10, $scale = 2);
                $table->dateTime('date');
                
                $table->dateTime('modified_at')->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'cash_book'){
        Schema::dropIfExists($tableName);
    }
}