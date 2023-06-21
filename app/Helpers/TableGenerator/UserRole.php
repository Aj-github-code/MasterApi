<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class UserRole{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id', $autoIncrement = false, $unsigned = false);
                $table->integer('role_id', $autoIncrement = false, $unsigned = false);
                $table->string('account_type', 255);
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->dateTime('modified_at')->nullable();
                
            });
         }
    }
    
}