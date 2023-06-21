<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class CampaignQuestions{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'campaign_questions'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->enum('question_type' , array('checkbox', 'radio', 'file', 'text'))->nullable();
                $table->string('question_code', 50)->nullable();
                $table->string('campaign_code', 50);
                $table->longtext('question');
                $table->string('slug', 50);
                $table->string('choice_type', 50)->default('single');
                
                $table->integer('weightage', $autoIncrement = false, $unsigned = false)->nullable();
                $table->tinyInteger('all_answer_mandatory')->default(1);
                $table->string('timer', 50)->default('00:05:00');
                
                
                
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at');
                $table->integer('created_by', $autoIncrement = false, $unsigned = false);
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'campaign_questions'){
        Schema::dropIfExists($tableName);
    }
    
}