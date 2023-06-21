<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Blog{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->string('image', 255)->nullable();
            $table->string('banner_image', 255)->nullable();
            $table->string('slug', 255)->unique();
            $table->longText('description');
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
        });
    }
    
}