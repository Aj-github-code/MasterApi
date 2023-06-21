<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Brands{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_name', 255);
            $table->string('logo', 255)->nullable();
            $table->longText('description');
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
        });
    }
    
}