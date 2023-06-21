<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use DB;

class getCompanyBySubDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        /*commented by Deepak
        $host = explode('.', $_SERVER['SERVER_NAME']);
            $hostName = '';
         if(count($host)==4){
                $hostName = str_replace('-', '', $host[1]);
        } elseif(count($host)==3){
            if($host[0] !== 'www'){
                $hostName = str_replace('-', '', $host[0]);
            } else {
                  $hostName = str_replace('-', '', $host[1]);
            }
        } elseif(count($host) == 2){
                $hostName = str_replace('-', '', $host[0]);
            
        }*/
        //$hostName = [];
        DB::enableQueryLog();
        //echo 'select * from companies where website LIKE "%'.$_SERVER['SERVER_NAME'].'%"';
        $prefix = DB::table('companies')->select(['tbl_prefix', 'website'])->where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
        //Log::error("prefix=".json_encode($prefix));
        if(NULL!==$prefix){
            session([$_SERVER['SERVER_NAME'].'.company_table_name'=>$prefix->tbl_prefix.'_']);
            session([$_SERVER['SERVER_NAME'].'.company_name'=>$prefix->website]);
        }
        return $next($request);
    }
}
