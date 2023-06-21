<?php
namespace App\Helpers\Tables;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Tags{
    public function __construct($params = array()){
    }
    
    public static function create($tableName = 'tags'){
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('tag', 200);
                $table->longtext('data', 12);
                
                
                $table->tinyInteger('is_active')->default(1);
                $table->dateTime('created_at');
                $table->integer('created_by', $autoIncrement = false, $unsigned = false);
                $table->dateTime('modified_at')->nullable();
                $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
                
            });
        }
    }
    
}