<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\setup;
use App\Models\Testimonials;
use App\Models\Vehicles;
use App\Models\product;
use App\Models\ProductCategories;
use App\Models\Roles;
use App\Models\User;
use App\Models\Enquiry;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Helpers\Helper as Helper;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

class DashboardController extends Controller
{
    //
    
    public function index(Request $request){
         $count['vehicle'] = DB::table('vehicles')->select(['vehicles.*', 'vm.name as vehicle_model', 'vms.name as vehicle_make'])
                   ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
                   ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')->count();
                   $product = product::where('id','!=',' ');
        $count['product']['total'] = $product->count();
        $count['product']['active'] = $product->where('is_active','=','1')->count();
        $count['product']['inactive'] = product::where('is_active','=','0')->count();
         
        $query = ProductCategories::where('is_service', '=', '1');
        
        $count['services']['total'] =  $query->orderBy('created', 'desc')->count();
        $count['services']['active'] =  $query->where('is_active', 'LIKE', '1')->orderBy('created', 'desc')->count();
        $count['services']['inactive'] =  ProductCategories::where('is_service', '=', '1')->where('is_active', 'LIKE', '0')->orderBy('created', 'desc')->count();
         $user = User::where('id','!=',' ');
        $count['user']['total'] = $user->count();
        $count['user']['active'] = $user->where('is_active','=','1')->count();
        $count['user']['inactive'] = User::where('is_active','=','0')->count();
        $count['enquiry'] = Enquiry::where('is_active','=','1')->count();
        $count['testimonial'] = Testimonials::where('is_active','=','1')->count();
                   //echo "<pre>";print_r($count);exit;
        try {
            return response()->json(['status'=>'success','data'=>$count]);

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
}
