<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class UserExamResults{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'user_exam_results'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id', $autoIncrement = false, $unsigned = false);
            $table->string('exam_code', 50);
            $table->string('campaign_code', 50);
            $table->integer('marks', $autoIncrement = false, $unsigned = false);
          
            $table->enum('status', array('PASS', 'FAIL'))->default('FAIL');
            
         
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        }
    }
    
    public static function delete($tableName = 'user_exam_results'){
        Schema::dropIfExists($tableName);
    }
}