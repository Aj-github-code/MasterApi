<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class UserReportingManager{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'user_reportingmanager'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id', $autoIncrement = false, $unsigned = false);
            $table->integer('rm_id', $autoIncrement = false, $unsigned = false);
         
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        }
    }
    
    public static function delete($tableName = 'user_reportingmanager'){
        Schema::dropIfExists($tableName);
    }
}