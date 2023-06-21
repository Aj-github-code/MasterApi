<?php

namespace App\Http\Controllers;

use App\Models\setup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;


class SetupController extends Controller
{
    
    public function index(Request $request)
    {
      
        //echo "hii";exit;
    
        $postData = $request->all();
        print_r($postData);exit;
        $data['type'] = (NULL !== $postData['type'])?'frontend':$postData['type'];
        try {
            $setup = Setup::select('*')->where('type',$data['type']);
            if(NULL!==$request->post('module')){
                $setup->where('module_name', $request->post('module'));
            }
            
            if(NULL!==$request->post('config')){
                foreach($request->post('config') as $cKey=>$config){
                    /*if(is_array($config)){
                        //logic to handle multiple config should be written here
                    }else{*/
                        $setup->where('config', 'LIKE', "%".$cKey.":".$config."%");
                    //}
                }
            }
            $setup->where('is_active', '1')->get();

             if($setup){
                return response()->json(['status'=>'success','data'=>$setup]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            //$error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
        
    public function createSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_name' => 'required',
            'parameter' => 'required',
            'value' => 'required',
            'datatype' => 'required',
            'priority' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
   
       $input = $request->all();
       
        $postData['module_name'] = $input['module_name'];
        $postData['parameter'] = $input['parameter'];
        $postData['value'] = $input['value'];
        $postData['datatype'] = $input['datatype'];
        $postData['priority'] = $input['priority'];
        $postData['created'] = date('Y-m-d H:i:s');
       
        
        // print_r($postData);exit;
        try {
            $post = setup::updateOrCreate(['parameter'=>$postData['parameter']],$postData);

            if($post){
                return response()->json([
                    'status' =>  "success",
                    'message' => "Setup Successfully"
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
    
    
    public function destroySetup(Request $request)
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
        $product = DB::table('setup')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($product){
            $product = DB::table('setup')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg
        ],200);
      
    }
    
    public function getModuleList(Request $request)
    {
      
    //   echo "hii";exit;
     try {
            $users = DB::table('setup')->select('module_name')->where('is_active', 'LIKE', '1')->get();

             if($users){
                return response()->json(['status'=>'success','data'=>$users]);
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
}
