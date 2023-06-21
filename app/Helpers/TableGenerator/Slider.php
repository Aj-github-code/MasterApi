<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Slider{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('slider_code', 50);
            $table->longtext('js')->nullable();
            $table->longtext('css', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->dateTime('modified')->nullable();
            
        });
    }
    
}