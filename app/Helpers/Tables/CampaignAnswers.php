<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class CampaignAnswers{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'campaign_answers'){
         if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('question_id', $autoIncrement = false, $unsigned = false);
                $table->longtext('answers');
                $table->tinyInteger('is_ans')->default(0);
                
                
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at')->nullable();
                $table->integer('created_by', $autoIncrement = false, $unsigned = false)->nullable();
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'campaign_answers'){
        Schema::dropIfExists($tableName);
    }
}