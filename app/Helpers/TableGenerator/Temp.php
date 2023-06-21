<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Temp{
    
    protected $tableName;
    protected $paymentKeys;
    
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
        $table->increments('id');
        $table->string('company_name');
        $table->char('company_code', '12');
        $table->string('slug');
        $table->string('first_name');
        $table->string('middle_name');
        $table->string('surname');
        $table->string('primary_email');
        $table->string('secondary_email');
        $table->string('contact_1');
        $table->string('contact_2');
        $table->string('type');
        $table->string('nature_of_business');
        $table->text('company_address');
        $table->timestamps();
    });
    }
    
}
