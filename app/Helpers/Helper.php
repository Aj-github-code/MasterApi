<?php
namespace App\Helpers;
use App\Helpers\Helper as Helper;
use Illuminate\Http\Request;
use Session;

use DB;
class Helper{
    protected $helper;
    protected $paymentKeys;
    //private $database;
    public function __construct($params = array())
    {
        $this->helper =& get_instance();
    }
    
    public static function getCompany(){
        
        //print_r($subdomain);
        $company = '';
        if(session($_SERVER['SERVER_NAME'].'company_table_name') && !empty(session($_SERVER['SERVER_NAME'].'company_table_name'))){
            //echo "jjj";
               $company =  session($_SERVER['SERVER_NAME'].'company_table_name');
            // if(session('company_table_name')){
                
            // }
        }
        else{
            //echo "kkkk";
            $prefix = DB::table('companies')->select(['tbl_prefix', 'website'])->where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
            //Log::error("prefix=".json_encode($prefix));
            if(NULL!==$prefix){
                $company = $prefix->tbl_prefix."_";
                session([$_SERVER['SERVER_NAME'].'.company_name'=>$prefix->website]);
                session([$_SERVER['SERVER_NAME'].'.company_table_name'=>$company]);
            }
        }
        //echo $company; exit;
        return $company;
    }
    
    public static function getCompanyDir(){
        list($subdomain,$host) = explode('.', $_SERVER["SERVER_NAME"]);
        return $subdomain.'_';
    }
    
    public static function get_fiscal_year($date = NULL){
        if(NULL===$date){
            $date = date('Y-m-d');
        }
        $billYear = date('y', strtotime($date)).'-'.date('y', strtotime($date.' +1 year'));
        if(date('m', strtotime($date))<=3)
            $billYear = date('y', strtotime($date.' -1 year')).'-'.date('y', strtotime($date));

        return $billYear;
    }
    
    public static function generateInvoice($organiserId = NULL, $type = 'GST') {
         $orderYear = self::get_fiscal_year();
       
        $orderCount = DB::table('orders')->select('id')->where('fiscal_yr', 'LIKE', $orderYear)
            ->where('organiser_id', 'LIKE', $organiserId)
            ->where('invoice_no', 'LIKE', $type.'/%')->count();
        
        $invoiceCode = DB::table('users')->select('referal_code')->where('id', '=', $organiserId)->first();
		$orderId = $orderCount+1;
        $orderCode = $type.'/'.$orderYear.'/'.$invoiceCode->referal_code.'/'.$orderId;

        return $orderCode;
    }
    
    public static function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    
    
    public static function decrypt($text){
        

        
        $iv = 'a1a2a3a4a5a6a7a8b1b2b3b4b5b6b7b8';
        $key = 'c1c2c3c4c5c6c7c8d1d2d3d4d5d6d7d8c1c2c3c4c5c6c7c8d1d2d3d4d5d6d7d8';
        $ct = $text;
        
        $ivBytes = hex2bin($iv);
        $keyBytes = hex2bin($key);
        $ctBytes = base64_decode($ct);
        
        $decrypt = openssl_decrypt($ctBytes, "aes-256-cbc", $keyBytes, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $ivBytes);
        
        $regex = "";
        
        $decrypt = preg_replace('/[\x00-\x1F\x7F]/u', '' ,$decrypt);
        return $decrypt;
    }
}