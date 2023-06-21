<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Testimonial{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 50);
            $table->string('image', 255);
            $table->longtext('description')->nullable();
            $table->integer('priority', $autoIncrement = false, $unsigned = false);
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
    }
    
}