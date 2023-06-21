<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;


class User{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('org_type', 255)->nullable();
            $table->string('organisation', 255)->nullable();
            
            $table->string('profile_image', 255);
            $table->string('email', 255);
            $table->string('mobile', 12);
            $table->longtext('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 150)->nullable();
            $table->string('district', 150)->nullable();
            $table->integer('pincode', $autoIncrement = false, $unsigned = false)->length(6)->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('lat', 20)->nullable();
            $table->string('lng', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            
            $table->integer('lock_out')->default(0);
            $table->dateTime('lock_out_time')->nullable();
            $table->integer('failed_login')->default(0);
            
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        
   
    }
    
    public function createUser(){
        
    }
}