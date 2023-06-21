<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Campaign{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'campaigns'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->default(0);
            $table->string('campaign_type', 255)->default('FAQ');
            $table->string('campaign_code', 255);
            $table->longtext('other_parameter');
            $table->dateTime('start_date')->nullable();
            $table->string('slug', 50)->nullable();
            
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by', $autoIncrement = false, $unsigned = false)->nullable();
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        }
    }
    
    public static function delete($tableName = 'campaigns'){
        Schema::dropIfExists($tableName);
    }
}