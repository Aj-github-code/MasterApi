<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Countries;
use App\Models\States;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;



class StatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $conditions = ['states.is_active'=>1];
            $searchPost = $request->post('search')??[];
            //print_r($searchPost);exit;
            $countryId = '';
            if(!empty($searchPost) && isset($request->post('search')['country_id']) && $request->post('country_id')!==0){
                $countryId = $request->post('search')['country_id'];
            }
            
            $query = States::select('*')->OfCountryWiseStates($countryId);
            if(NULL!==$request->post('child') && $request->post('child')){
                $query->with('country');    
            }
            
            $states = $query->get();
            if($states){
                return response()->json(['status'=>'success','data'=>$states]);
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
            'country_id' => 'required',
            'state_name' => 'required',
            'gst_state_code' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        $input['slug'] = Str::slug($input['state_name']);
        try {
            $post = States::updateOrCreate(['slug'=>$input['slug']],$input);

            if($post){
                
                return response()->json([
                    'status' => "success",
                    'message' => "State Created Successfully",
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
    
    public function delete(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'state' => 'required',
            'enable'=>'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error.'.$validator->errors()]);
        }
        
        $postData['is_active'] = (NULL !== $request->post('enable'))?$request->post('enable'):0;
        $postData['modified'] = date('Y-m-d H:i:s');
        
        $state = States::select('*')->where('slug', $request->post('state'))->update($postData);

        $msg = 'No Data to Update';
        if($state){
            $state = States::select('*')->where('slug', $request->post('state'))->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json(['status' =>  "success", 'message' => $msg,'data' => $state],200);
    }
    
    public function edit(Request $request, $slug=NULL)
    {
        //print_r(States::where('slug', $slug)->first());//exit;
        if(NULL===$slug || empty(States::where('slug', $slug)->first())){
            
            return response()->json(['status'=>'error','message'=>'Invalid Request']);
        }
        
        //exit;
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'state_name' => 'required',
            'gst_state_code' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        
        $input['slug'] = Str::slug($input['state_name']);
        try {
            $post = States::where('slug', $slug)->update($input);

            if($post){
                $post = States::where('slug', $input['slug'])->first();
                return response()->json([
                    'status' => "success",
                    'message' => "State Updated Successfully",
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
}