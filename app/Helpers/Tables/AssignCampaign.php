<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class AssignCampaign{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'assign_campaign'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id', $autoIncrement = false, $unsigned = false);
                $table->string('campaign_code', 50);
                $table->tinyInteger('status')->default(1);
                
                $table->dateTime('created_at');
                $table->integer('created_by', $autoIncrement = false, $unsigned = false);
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
    public static function delete($tableName = 'assign_campaign'){
        Schema::dropIfExists($tableName);
    }
}