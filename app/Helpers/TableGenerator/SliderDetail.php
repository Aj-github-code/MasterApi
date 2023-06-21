<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class SliderDetail{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('slider_id', 50);
            $table->string('filter_text', 15);
            $table->string('type')->nullable();
            $table->string('title_1', 150);
            $table->string('title_2', 150)->nullable();
            $table->string('short_description', 150);
            $table->string('image', 150);
            $table->integer('priority', $autoIncrement = false, $unsigned = false);
            $table->string('link', 255);
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->dateTime('modified')->nullable();
            
        });
    }
    
}