<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setup;
use App\Models\Slider;
use App\Models\Sliderdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Helpers\Helper as Helper;
use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

use App\Helpers\TableMaker\QNAMgmt as QNAMgmtCreator;
use App\Helpers\TableMaker\DeliveryMgmt as DeliveryMgmtCreator;

//use App\Helpers\Helper as Helper;

class SetupController extends Controller
{
    public $companyFilePath = "";
    
    public $companyData = [];
    
    public function __construct(Request $request){
        $this->companyData = json_decode(json_encode((new CompanyController)->view($request, (NULL!==$request->post('company_code'))?$request->post('company_code'):NULL)), true);
        if($this->companyData['original']['status']=="success" && !empty($this->companyData['original']['data'])){
            $this->companyFilePath = URL('public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/');
        }else{
            return response()->json(['status'=>'error','message'=>'Invalid Request']);
        }
        
    }
    /*  public function index(Request $request)
    {
      
       //echo "hii";exit;
    
    $postData = $request->all();
    
    $data['type'] = (NULL !== $request->post('type'))?$request->post('type'):'frontend';
     try {
            $users = DB::table('setup')->select('*')->where('type',$data['type'])->where('is_active', '1')->get();

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
    }*/
    public function index(Request $request)
    {  
        //echo "hii";
        DB::enableQueryLog();
        $postData = $request->all();
        
        $dataType['type'] = (NULL !== $request->post('type'))?$request->post('type'):'frontend';
        try {
            
            $query = Setup::where('type', 'LIKE', $dataType['type']);
            if(NULL!==$request->post('module_name')){
                //echo "hii";
                $query->where('module_name', 'LIKE', $request->post('module_name'));
            }
            
            if(NULL!==$request->post('config')){
                if(is_array($request->post('config'))){
                    foreach($request->post('config') as $cKey=>$config){
                        /*if(is_array($config)){
                            //logic to handle multiple config should be written here
                        }else{*/
                            $query->where('config', 'LIKE', "%".$config."%");
                        //}
                    }
                }else{
                    $query->where('config', 'LIKE', "%".$request->post('config')."%");
                }
                
            }
            //$setup = $query->where('is_active', '1')->get();
            $setup = $query->where('is_active', '1')->get()->toArray();
            //print_r(DB::getQueryLog());
             
           
            $res = [];
            if($setup){
                /*print_r($setup);
             exit;*/
                //echo $this->companyFilePath;
                foreach($setup as $skey => $value){
                    //print_r($value['config']);exit;
                    $config = json_decode($value['config'], true);
                     //print_r($value);exit;
                    if($value['type']=="frontend"){
                        //print_r($value);exit;
                        if($value['module_name']=="Website"){
                        
                        }elseif($value['module_name']=="Aboutus"){
                            //team image
                            // exit;
                             //return $config['about']['team_image'];
                             //print_r($config);exit;
                            if($config['about']['team_image']!=""){
                                //echo $this->companyFilePath;
                                $path = $this->companyFilePath.'/aboutus_image/'.$config['about']['team_image'];
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = @file_get_contents($path);
                                if($data !== FALSE){
                                    $config['about']['team_image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                                }else{
                                    $config['about']['team_image'] = "";
                                }
                                $config['about']['team_image'] = $path;
                                
                            }
                            
                            if($config['mission']['mission_image']!=""){
                                //echo $this->companyFilePath;
                                $path = $this->companyFilePath.'/aboutus_image/'.$config['mission']['mission_image'];
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = @file_get_contents($path);
                                if($data !== FALSE){
                                    $config['mission']['mission_image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                                }else{
                                    $config['mission']['mission_image'] = "";
                                }
                                $config['mission']['mission_image'] = $path;
                                
                            }
                            
                            if($config['vision']['vision_image']!=""){
                                //echo $this->companyFilePath;
                                $path = $this->companyFilePath.'/aboutus_image/'.$config['vision']['vision_image'];
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = @file_get_contents($path);
                                if($data !== FALSE){
                                    $config['vision']['vision_image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                                    //$config['vision']['vision_image'] = $path;
                                }else{
                                    $config['vision']['vision_image'] = "";
                                }
                                $config['vision']['vision_image'] = $path;
                                
                            }
                            
                            
                            if(count($config['additional_info'])>0){
                                foreach($config['additional_info'] as $iKey=>$info){
                                    //print_r($info);
                                    if(array_key_exists('process_image', $info)){
                                        if($info['process_image']!=""){
                                            //echo $this->companyFilePath;
                                            $path = $this->companyFilePath.'/aboutus_image/'.$info['process_image'];
                                            $path = str_replace(" ","%20", $path);
                                            $type = pathinfo($path, PATHINFO_EXTENSION);
                                            $data = @file_get_contents($path);//print_r($data);
                                            if($data !== FALSE){
                                                $config['additional_info'][$iKey]['process_image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                                            }else{
                                                $config['additional_info'][$iKey]['process_image'] = "";
                                            }
                                            $config['additional_info'][$iKey]['process_image'] = $path;
                                        }
                                    }
                                    
                                    if(array_key_exists('process_video', $info)){
                                        if($info['process_video']!=""){
                                            //echo $this->companyFilePath;
                                            $path = $this->companyFilePath.'/aboutus_image/'.$info['process_video'];
                                            //print_r();
                                            $type = pathinfo($path, PATHINFO_EXTENSION);
                                            $path = str_replace(" ","%20", $path);
                                            $data = @file_get_contents($path);
                                            $data =  base64_encode($data);
                                            if(strlen($data) > 0){
                                            //if($data !== FALSE){
                                                //$config['additional_info'][$iKey]['process_video'] = 'data:video/' . $type . ';base64,' . $data;
                                                $config['additional_info'][$iKey]['process_video'] = $path;
                                            }else{
                                                $config['additional_info'][$iKey]['process_video'] = "";
                                            }
                                            
                                            $config['additional_info'][$iKey]['process_video'] = $path;
                                        }
                                    }
                                    
                                    if(array_key_exists('research_image', $info)){
                                        if($info['research_image']!=""){
                                            //echo $this->companyFilePath;
                                            $path = $this->companyFilePath.'/aboutus_image/'.$info['research_image'];
                                            $type = pathinfo($path, PATHINFO_EXTENSION);
                                            $data = @file_get_contents($path);
                                            if($data !== FALSE){
                                                $config['additional_info'][$iKey]['research_image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                                            }else{
                                                $config['additional_info'][$iKey]['research_image'] = "";
                                            }
                                            $config['additional_info'][$iKey]['research_image'] = $path;
                                        }
                                    }
                                }
                            }
                            
                        }
                    }elseif($value['type']=="application"){
                        
                    }elseif($value['type']=="backend"){
                        
                    }
                    //print_r($config);exit;
                    $setup[$skey]['config'] = $config;
                    $res[strtolower($value['type'].'-'.$value['module_name'])] = $setup[$skey];
                }
                return response()->json(['status'=>'success','data'=>$res]);
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
    
    public function modulewisedata(Request $request)
    {
        DB::enableQueryLog();
        $postData = $request->all();
        
        $data['type'] = (NULL !== $postData['type'])?$postData['type']:'application';
        try {
            
            /*echo public_path();
            
            exit;*/
            $query = Setup::where('type', 'LIKE', $data['type']);
            if(NULL!==$request->post('module')){
                //echo "hii";
                $query->where('module_name', 'LIKE', $request->post('module'));
            }
            
            if(NULL!==$request->post('config')){
                foreach($request->post('config') as $cKey=>$config){
                    /*if(is_array($config)){
                        //logic to handle multiple config should be written here
                    }else{*/
                        $query->where('config', 'LIKE', "%".$cKey.":".$config."%");
                    //}
                }
            }
            // $query->where('is_active', '1');
            $setup = $query->first();
            //print_r($setup);exit;
            //print_r(DB::getQueryLog());exit;
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
        
    /*public function create(Request $request)
    {
        $company = session('company_table_name');
        //echo 'hello';exit;
        $validator = Validator::make($request->all(), [
            'module_name' => 'required',
            'type' => 'required',
            'config' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        $input = $request->all();
        //echo json_encode($input);exit;
            if($request->hasFile('config.site_settings.logo')){
                $files = $request->file('config.site_settings.logo');
                $original_filename = $files->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                //  print_r($files);exit;
                $file_type = $files->getMimeType();
                // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                              
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
            
                if ($files->move('./public/upload/setup/', $image)) {
                    // echo $image;exit;
                    $input['config']->site_settings->logo = $image;
                }
                // }
                
               
            
            }
            //return $input;
        $postData['type'] = $input['type'];
        $postData['module_name'] = $input['module_name'];
        $postData['config'] = json_encode($input['config'], true);
        $postData['is_active'] = 1;
     
        //  print_r($postData);exit;
        $checkpostData = DB::table($company.'setup')->select('*')->where(['type'=>$input['type'],'module_name'=>$input['module_name']])->count();
        
        
        //print_r($postData);exit;
       
        
         
        try {
            $post = setup::updateOrCreate(['type'=>$postData['type'],'module_name'=>$postData['module_name']],$postData);


             if($checkpostData > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $postData['modified_by'] = auth()->id();
                
                if(isset($input['config']['site_settings']) && (NULL !== $input['config']['site_settings'])){
                   if(isset($input['config']['site_settings']['logo']) && (NULL !== $input['config']['site_settings']['logo'])){
                        $input['config']['site_settings']['logo'] = URL('/public/upload/setup/'.$input['config']['site_settings']['logo']);
                   }
                }
                $postData['config'] = json_encode($input['config'], true);
                
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "Setup updated Successfully",
                    'data' => $postData['config']
                ],200);
            }else{
                $postData['created'] = date('Y-m-d H:i:s');
                $postData['created_by'] = auth()->id();
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "Setup created Successfully"
                ],200);
            }
            
            if($post){
                return $response;
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }

    }*/
    
    public function create(Request $request)
    {
        //return response()->json(['status'=>'error','message'=>$request->all()]);
        $company = Helper::getCompany();
        //echo $company;exit;
        //echo 'hello';exit;
        $validator = Validator::make($request->all(), [
            'module_name' => 'required',
            'type' => 'required',
            'config' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        $input = $request->all();
        
        $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
        //return response()->json(['status'=>'error','message'=>$companyData]);
        if($request->hasFile('config.site_settings.logo')){
            $files = $request->file('config.site_settings.logo');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/profile_image/', $image)) {
                    // echo $image;exit;
                    //$input['config']->site_settings->logo = $image;//commented by Deepak J.
                    $input['config']['site_settings']['logo'] = $image;
                }
            // }
        }
        
        //return response()->json(['status'=>'error','message'=>$request->post('config')['theme']]);
            if($request->post('config')['theme']=="dealer" && $request->hasFile('config.site_settings.manufacturer_logo')){
                
                $dfiles = $request->file('config.site_settings.manufacturer_logo');
                $original_filename = $dfiles->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $dfile_ext = end($original_filename_arr);
                //  print_r($dfiles);exit;
                $file_type = $dfiles->getMimeType();
                // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                	$dimage = $original_filename_arr[0].time(). '.'.$dfile_ext;
                    if ($dfiles->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/profile_image/', $dimage)) {
                        // echo $image;exit;
                        //$input['config']->site_settings->manufacturer_logo = $image;//commented by Deepak J.
                        $input['config']['site_settings']['manufacturer_logo'] = $dimage;
                    }else{
                        $input['config']['site_settings']['manufacturer_logo'] = '';
                    }
                // }
            }/*else{
                $input['config']['site_settings']['manufacturer_logo'] = '';
            }*/
        
       
        //return $input;
        
        $postData['type'] = $input['type'];
        $postData['module_name'] = $input['module_name'];
        $postData['config'] = json_encode($input['config'], true);
        $postData['is_active'] = 1;
        
        //return response()->json(['status'=>'error','message'=>$postData]);
        //  print_r($postData);exit;
        $checkpostData = DB::table($company.'setup')->select('*')->where(['type'=>$input['type'],'module_name'=>$input['module_name']])->count();
        
        try {
                
            $post = setup::updateOrCreate(['type'=>$postData['type'],'module_name'=>$postData['module_name']],$postData);

            if($checkpostData > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $postData['modified_by'] = auth()->id();
                
                if(isset($input['config']['site_settings']) && (NULL !== $input['config']['site_settings'])){
                   if(isset($input['config']['site_settings']['logo']) && (NULL !== $input['config']['site_settings']['logo'])){
                        //$input['config']['site_settings']['logo'] = URL('/public/upload/setup/'.$input['config']['site_settings']['logo']);
                   }
                }
                $postData['config'] = json_encode($input['config'], true);
                $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
                return $response = response()->json([
                    'status' =>  "success",
                    'message' => "Setup updated Successfully",
                    'data' => $companyData['original']['data']
                ]);
            }else{
                $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
                $postData['created'] = date('Y-m-d H:i:s');
                $postData['created_by'] = auth()->id();
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "Setup created Successfully",
                    'data' => $companyData['original']['data']
                ]);
            }
            
            if($post){
               
                return $response;
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
        $company = Helper::getCompany();
        
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
        $product = DB::table($company.'setup')->where('id', $input['id'])->update($postData);

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
        $company = Helper::getCompany();
        
    //   echo "hii";exit;
     try {
            $users = DB::table($company.'setup')->select('module_name')->where('is_active', 'LIKE', '1')->get();

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
    
    public function loadConfig(Request $request){
        try {
            //echo asset('public/config-json/'.$request->file_name);
            $config = json_decode(file_get_contents(asset('public/config-json/'.$request->file_name)), true);
            //print_r($config);exit;
            return response()->json(['status'=>'success','data'=>$config]);
            
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
        
    }   
    
    
    public function createAboutus(Request $request)
    {
        //return response()->json(['status'=>'error','message'=>$request->all()]);
        $company = Helper::getCompany();
        //echo $company;exit;
        //echo 'hello';exit;
        $validator = Validator::make($request->all(), [
            'module_name' => 'required',
            'type' => 'required',
            'config' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        $input = $request->all();
        //return $input;
        $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
        //return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors(),'data'=>$companyData]);  
        if($request->hasFile('config.about.team_image')){
            $files = $request->file('config.about.team_image');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
              //print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$team_image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $team_image)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['about']['team_image'] = $team_image;
                    //$input['config']['about']['team_image'] = "";
                }
            // }
        }elseif($input['config']['about']['team_image'] !== NULL){
            $image = pathinfo($input['config']['about']['team_image']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['about']['team_image'] = $image;
        }else{
            
            $input['config']['about']['team_image'] = "";
        }
        if($request->hasFile('config.mission.mission_image')){
            $files = $request->file('config.mission.mission_image');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$mission_image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $mission_image)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['mission']['mission_image'] = $mission_image;
                }else{
                    $input['config']['mission']['mission_image'] = "";
                }
            // }
        }elseif($input['config']['mission']['mission_image'] !== NULL){
            $image = pathinfo($input['config']['mission']['mission_image']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['mission']['mission_image'] = $image;
        }else{
            $input['config']['mission']['mission_image'] = "";
        }
        
        if($request->hasFile('config.vision.vision_image')){
            $files = $request->file('config.vision.vision_image');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$vision_image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $vision_image)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['vision']['vision_image'] = $vision_image;
                }else{
                    $input['config']['vision']['vision_image'] = "";
                }
            // }
        }elseif($input['config']['vision']['vision_image'] !== NULL){
            $image = pathinfo($input['config']['vision']['vision_image']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['vision']['vision_image'] = $image;
        }else{
            $input['config']['vision']['vision_image'] = "";
        }
        
        if($request->hasFile('config.additional_info.assentia.process_image')){
            $files = $request->file('config.additional_info.assentia.process_image');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$process_image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $process_image)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['additional_info']['assentia']['process_image'] = $process_image;
                }else{
                    $input['config']['additional_info']['assentia']['process_image'] = "";
                }
            // }
        }elseif(isset($input['config']['additional_info']['assentia']['process_image']) && (NULL !== $input['config']['additional_info']['assentia']['process_image'])){
           $image = pathinfo($input['config']['additional_info']['assentia']['process_image']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['additional_info']['assentia']['process_image'] = $image;
        }else{
            $input['config']['additional_info']['assentia']['process_image'] = "";
        }
        
        if($request->hasFile('config.additional_info.assentia.process_video')){
            $files = $request->file('config.additional_info.assentia.process_video');
            $original_filename = $files->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($files);exit;
            $file_type = $files->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$process_video = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($files->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $process_video)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['additional_info']['assentia']['process_video'] = $process_video;
                }else{
                    $input['config']['additional_info']['assentia']['process_video'] = "";
                }
            // }
        }elseif(isset($input['config']['additional_info']['assentia']['process_video']) && (NULL !== $input['config']['additional_info']['assentia']['process_video'])){
            $image = pathinfo($input['config']['additional_info']['assentia']['process_video']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['additional_info']['assentia']['process_video'] = $image;
        }else{
            $input['config']['additional_info']['assentia']['process_video'] = "";
        }
        
        if($request->hasFile('config.additional_info.assentia.research_image')){
            $rfiles = $request->file('config.additional_info.assentia.research_image');
            $original_filename = $rfiles->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            //  print_r($rfiles);exit;
            $file_type = $rfiles->getMimeType();
            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$research_image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($rfiles->move('./public/upload/'.$companyData['original']['data']['sub_domain'].'_files/aboutus_image/', $research_image)) {
                    // echo $image;exit;
                    //$input['config']->about->team_image = $image;//commented by Deepak J.
                    $input['config']['additional_info']['assentia']['research_image'] = $research_image;
                }else{
                    $input['config']['additional_info']['assentia']['research_image'] = "";
                }
            // }
        }elseif(isset($input['config']['additional_info']['assentia']['research_image']) && (NULL !== $input['config']['additional_info']['assentia']['research_image'])){
            $image = pathinfo($input['config']['additional_info']['assentia']['research_image']);
            $image = str_replace("%20"," ", $image['basename']);
            $input['config']['additional_info']['assentia']['research_image'] = $image;
        }else{
            $input['config']['additional_info']['assentia']['research_image'] = "";
        }
        
        //return $input['config'];
        
        $postData['type'] = $input['type'];
        $postData['module_name'] = $input['module_name'];
        $postData['config'] = json_encode($input['config'], true);
        $postData['is_active'] = 1;
        
        //return response()->json(['status'=>'error','message'=>$postData]);
          //print_r($postData);exit;
        $checkpostData = DB::table($company.'setup')->select('*')->where(['type'=>$input['type'],'module_name'=>$input['module_name']])->count();
        
        try {
            //echo 'hello';exit;
            $post = setup::updateOrCreate(['type'=>$postData['type'],'module_name'=>$postData['module_name']],$postData);


            if($checkpostData > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $postData['modified_by'] = auth()->id();
                
                if(isset($input['config']['about']) && (NULL !== $input['config']['about'])){
                   if(isset($input['config']['about']['team_image']) && (NULL !== $input['config']['about']['team_image'])){
                        //$input['config']['site_settings']['logo'] = URL('/public/upload/setup/'.$input['config']['site_settings']['logo']);
                   }
                }
               
                $postData['config'] = json_encode($input['config'], true);
                $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
                return $response = response()->json([
                    'status' =>  "success",
                    'message' => "About Us Setup updated Successfully",
                    'data' => $post
                ]);
            }else{
                $companyData = json_decode(json_encode((new CompanyController)->view($request, $request->post('company_code'))), true);
                $postData['created'] = date('Y-m-d H:i:s');
                $postData['created_by'] = auth()->id();
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "About Us Setup created Successfully",
                    'data' => $post
                ]);
            }
            
            if($post){
                return $response;
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

