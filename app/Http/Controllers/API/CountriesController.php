<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;



class CountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $conditions = ['countries.is_active'=>1];
            $searchPost = $request->post('search')??[];
            
            $query = Countries::select('*')->where('is_active', true);
            
            
            $countries = $query->get();
            if($countries){
                return response()->json(['status'=>'success','data'=>$countries]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'short_name' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        $input['slug'] = Str::slug($input['name']);
        try {
            $post = Countries::updateOrCreate(['slug'=>$input['slug']],$input);

            if($post){
                
                return response()->json([
                    'status' => "success",
                    'message' => "Country Created Successfully",
                    'data' => $post
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }

    }

    public function destroyCountry(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (NULL !== $input['is_active'])?0:1;
        
        $postData['modified'] = date('Y-m-d H:i:s');
        $country = DB::table('countries')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($country){
            $country = DB::table('countries')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg
        ],200);
      
    }
    
}