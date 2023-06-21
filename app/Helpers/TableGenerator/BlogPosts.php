<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class BlogPosts{
    public function __construct($params = array())
    {
        
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            //'id', 'blog_id', 'parent_id', 'post', 'is_active', 'created_at','created_by','modified_at','modified_by
            $table->increments('id');
            $table->integer('blog_id', $autoIncrement = false, $unsigned = false);
            $table->integer('parent_id')->default(0);
            $table->longText('post');
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
        });
    }
    
}