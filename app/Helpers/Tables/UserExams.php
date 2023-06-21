<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class UserExams{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'user_exams'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('exam_code', 50);
            $table->string('campaign_code', 50);
            $table->integer('question_id', $autoIncrement = false, $unsigned = false);
          
            $table->longtext('question_ans');
              $table->string('user_ans', 100);
                $table->integer('marks', $autoIncrement = false, $unsigned = false);
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        }
    }
    
    public static function delete($tableName = 'user_exams'){
        Schema::dropIfExists($tableName);
    }
}