<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

use App\Helpers\TableGenerator\Setup as Setup;

class Setup{
    public function __construct($params = array()){
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50);
            $table->string('module_name', 100);
            $table->longtext('config', 255);
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false);
            $table->dateTime('modified')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->nullable();
            
        });
        
        // Setup::insertDefault($tableName);
    }
    
    public static function insertDefault($postData = []){
        DB::insert('INSERT INTO `'.$postData['company_code'].'_setup` 
            ( `type`, `module_name`, `config`, `is_active`, `created`, `created_by`) 
            VALUES (?,?,?,?,?,?)',
            ['frontend', 
            'Website', 
            '{"site_settings":{
                "logo":"'.(isset($postData['logo'])?$postData['logo']:'logo-tvs1676284946.png').'",
                "color":{
                    "primary":"#1b3396",
                    "backgound":"#618ebf",
                    "dark":"#3072cd"
                    
                },
                "title":"'.(isset($postData['company_name'])?$postData['company_name']:'PRIMARYKEYTECHNOLOGIES').'",
                "Subtitle":"'.(isset($postData['company_name'])?$postData['company_name']:'YourTechnicalConsultant').'",
                "footer-text":"AllRightsReserved"
                
            },
            "theme":"rsa",
            "captcha_key":"",
            "lat":"",
            "lng":"",
            "template":"'.(isset($postData['theme'])?$postData['theme']:'dealer').'"
            }', 1, date('Y-m-d H:i:s'), auth()->user()->id]
        );
    }
    
}