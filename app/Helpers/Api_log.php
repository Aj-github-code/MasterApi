<?php
namespace App\Helpers;
use App\Helpers\Helper as Helper;
use Session;

use DB;
class Api_log{
    protected $helper;
    protected $paymentKeys;
    //private $database;
    public function __construct($params = array())
    {
        $this->helper =& get_instance();
    }
    
    public static function createLog($params){
        DB::table('api_log')->insert($params);
    }
    
}