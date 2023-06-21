<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Enquiry{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('phone', 12);
            $table->string('email', 150);
            $table->string('enquiry_type', 50);
            $table->string('enquiry_code', 50);
            $table->longtext('address')->nullable();
            $table->longtext('remark')->nullable();
            $table->longtext('data')->nullable();
            
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
    }
    
}