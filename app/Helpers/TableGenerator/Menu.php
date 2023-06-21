<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Menu{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            //`id`,  `module`, `is_active`, `created`, `modified`
            $table->increments('id');
            $table->enum('type', ['Frontend', 'Backend', 'Member']);
            $table->string('name', 255);
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->dateTime('modified')->nullable();
        });
        
        DB::table($tableName)->insert(
        [
            'type' => 'Frontend',
            'name' => 'Frontend Main Menu',
            'created'=>date('Y-m-d H:i:s')
        ],
        [
            'type' => 'Backend',
            'name' => 'Backend Left Menu',
            'created'=>date('Y-m-d H:i:s')
        ],
        [
            'type' => 'Frontend',
            'name' => 'Frontend-footer 1',
            'created'=>date('Y-m-d H:i:s')
        ],
        [
            'type' => 'Frontend',
            'name' => 'Frontend-footer 2',
            'created'=>date('Y-m-d H:i:s')
        ],
        [
            'type' => 'Frontend',
            'name' => 'Frontend-footer 3',
            'created'=>date('Y-m-d H:i:s')
        ],
        [
            'type' => 'Frontend',
            'name' => 'Frontend-footer 4',
            'created'=>date('Y-m-d H:i:s')
        ]    
        
    );
    }
    
}