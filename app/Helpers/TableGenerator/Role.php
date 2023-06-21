<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Role{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name', 50);
            $table->string('role_code', 10);
            $table->string('slug', 255);
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('modified_at')->nullable();
            
        });
    }
    
}