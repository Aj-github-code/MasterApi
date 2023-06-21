<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\UserCompany;
use App\Models\Setup;
use App\Models\States;
use App\Models\City;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Helpers\Helper as Helper;
use App\Helpers\TableGenerator\Gallery as Gallery;
use App\Helpers\TableGenerator\Product as Product;
use App\Helpers\TableGenerator\ProductCategory as ProductCategory;
use App\Helpers\TableGenerator\ProductImage as ProductImage;
use App\Helpers\TableGenerator\ProductProductCategory as ProductProductCategory;
use App\Helpers\TableGenerator\Role as Role;
use App\Helpers\TableGenerator\Setup as SetupConfig;
use App\Helpers\TableGenerator\Slider as Slider;
use App\Helpers\TableGenerator\SliderDetail as SliderDetail;
use App\Helpers\TableGenerator\Testimonial as Testimonial;
use App\Helpers\TableGenerator\User as User;
use App\Helpers\TableGenerator\Enquiry as Enquiry;
use App\Helpers\TableGenerator\Menu as Menu;
use App\Helpers\TableGenerator\Navigation as Navigation;
use App\Helpers\TableGenerator\CompanyBranch as Companybranch;
use App\Helpers\TableGenerator\Blog as Blog;
use App\Helpers\TableGenerator\BlogCategories as BlogCategories;
use App\Helpers\TableGenerator\BlogFiles as BlogFiles;
use App\Helpers\TableGenerator\BlogPosts as BlogPosts;
use App\Helpers\TableGenerator\BlogsCategories as BlogsCategories;
use App\Helpers\TableGenerator\Team as Team;
use App\Helpers\TableGenerator\Brands as Brands;
use App\Helpers\TableGenerator\UserRole as UserRoles;

use Illuminate\Support\Str;
use DB;

use App\Helpers\TableMaker\QNAMgmt as QNAMgmtCreator;
use App\Helpers\TableMaker\DeliveryMgmt as DeliveryMgmtCreator;


class CompanyController extends BaseController
{
    private $pub_html;
    private $profileImagePath;
    public function __construct(){
        $this->profileImagePath = "./storage/app/public/profile_image/";
    }
    
    public function list(Request $request)
    {
        $postData = $request->all();
        try {
           $query = DB::table('companies')->select(['companies.*']);
            
            $companies = $query->where('companies.is_active', 'LIKE', '1')->orderBy('companies.created', 'DESC')->get();
            foreach($companies as $cKey=>$company){
                $chkTbl = Setup::checkCompanySetup($company->tbl_prefix);
                $companies[$cKey]->logo = URL('/').'/public/upload/company/'.$company->logo;
                $companies[$cKey]->company_address = json_decode($company->company_address, true);
                if(!empty($chkTbl)){
                    $setup = Setup::getCompanySetup($company->tbl_prefix);
                    //print_r($setup);
                    $config = [];
                    if($setup){
                        $config = json_decode($setup->config, true);
                        $config['site_settings']['logo'] = URL('/').'/public/upload/'.$company->sub_domain.'_files/profile_image/'.$config['site_settings']['logo'];//base64_encode($img);
                        $config['site_settings']['manufacturer_logo'] = (isset($config['site_settings']['manufacturer_logo']))?URL('/').'/public/upload/'.$company->sub_domain.'_files/profile_image/'.$config['site_settings']['manufacturer_logo']:'';//base64_encode($img);
                    }
                    //print_r($setup);
                    
                    $companies[$cKey]->website_config = $config;
                }else{
                    $companies[$cKey]->website_config = [];
                }
                
            }
            
            if($companies){
                return response()->json(['status'=>'success','data'=>$companies]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1], 'db_error'=>$e->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }

    /*public function view(Request $request, $slug=NULL){
        $user = [];
        if(NULL===$slug){
            $user = Company::where('website','LIKE',"%{$_SERVER['SERVER_NAME']}%")->where('is_active', true)->first();
        }else{
            $user = DB::table('companies')
            ->select(['*'])
            ->where('companies.company_code','LIKE', str_replace('-', '',$slug))->first();
        }
        
        //print_r($user);exit;
        
        if($user){
            $user->logo = URL('/').'/public/upload/company/'.$user->logo;
            $user->about_company_image = URL('/').'/public/upload/company/'.$user->about_company_image;
            $user->company_mission_image = URL('/').'/public/upload/company/'.$user->company_mission_image;
            $user->company_vision_image = URL('/').'/public/upload/company/'.$user->company_vision_image;
            $user->company_address = json_decode($user->company_address, true);
            $chkTbl = Setup::checkCompanySetup($user->tbl_prefix);
                
            if(!empty($chkTbl)){
                $setup = Setup::getCompanySetup($user->tbl_prefix);
                $config = json_decode($setup->config, true);
                $config['site_settings']['logo'] = base64_encode(URL('/').'/public/upload/'.$user->sub_domain.'/profile_image/'.$config['site_settings']['logo']);
                //print_r($setup);
                
                $user->website_config = $config;
            }else{
                $user->website_config = [];
            }
            //return json_encode(['status'=>'success', 'data'=>$user[0]]);
               return response()->json(['status'=>'success','data'=>$user]);
        }
    }*/
    
    public function view(Request $request, $slug=NULL){
        //echo $_SERVER['SERVER_NAME'];
        $user = [];
        if(NULL===$slug){
            $user = Company::where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
        }else{
            $user = DB::table('companies')
            ->select(['*'])
            ->where('companies.company_code','LIKE', str_replace('-', '',$slug))->first();
        }
        
        
        
        if($user){
            $user->logo = URL('/').'/public/upload/company/'.$user->logo;
            $user->about_company_image = URL('/').'/public/upload/company/'.$user->about_company_image;
            $user->company_mission_image = URL('/').'/public/upload/company/'.$user->company_mission_image;
            $user->company_vision_image = URL('/').'/public/upload/company/'.$user->company_vision_image;
            $user->company_address = json_decode($user->company_address, true);
            $user->company_bank = json_decode($user->company_bank, true);
            $user->company_socialMedia = json_decode($user->company_socialMedia, true);//by kanak
            $address = $user->company_address;
            if(!empty($address)){
                foreach($address as $aKey=>$add){
                    $state = States::find($add['state'])->select('state_name')->first();
                    $city = City::find($add['city'])->select('city_name')->first();
                    $add['state_name'] = $state->state_name;
                    $add['city_name'] = $city->city_name;
                    $user->company_address = [$aKey=>$add];
                }
            }
            $chkTbl = Setup::checkCompanySetup($user->tbl_prefix);
                
            if(!empty($chkTbl)){
                $setup = Setup::getCompanySetup($user->tbl_prefix);
                //return response()->json(['status'=>'success','data'=>$setup]);
                if($setup){
                    $config = json_decode($setup->config, true);
                    if(isset($config['theme'])){
                        if($config['theme']=="dealer"){
                            $config['site_settings']['manufacturer_logo'] = (isset($config['site_settings']['manufacturer_logo']))?((strpos($config['site_settings']['manufacturer_logo'], 'http') === 0)?$config['site_settings']['manufacturer_logo']:URL('/').'/public/upload/'.$user->sub_domain.'_files/profile_image/'.$config['site_settings']['manufacturer_logo']):'';   
                        }else{
                            $config['site_settings']['manufacturer_logo'] = "";
                        }
                        $config['site_settings']['logo'] = (strpos($config['site_settings']['logo'], 'http') === 0)?$config['site_settings']['logo']:URL('/').'/public/upload/'.$user->sub_domain.'_files/profile_image/'.$config['site_settings']['logo'];
                            
                        $user->website_config = $config;
                    }
                    
                    
                }else{
                    $user->website_config = [];
                }
            }else{
                $user->website_config = [];
            }
            //return json_encode(['status'=>'success', 'data'=>$user[0]]);
            return response()->json(['status'=>'success','data'=>$user]);
        }else{
            return response()->json(['status'=>'error','message'=>"Invalid Access"]);  
        }
    }
    
    public function create_update(Request $request){
    
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        $input = $request->all();
    
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
        } else {
            $postData['id'] = '';
        }
        
        $postData['company_name'] = $input['company_name'];
        if(isset($postData['short_code']) && (NULL !== $postData['short_code'])){
             $input['short_code'] = $postData['short_code'];
        } else {
            //$input['short_code'] = $this->generateSystemCode(isset($postData['company_name'])?$postData['company_name']:'TVS');
        }
        if($request->hasFile('logo')){
            $original_filename = $request->file('logo')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('logo')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('logo')->move('./public/upload/company/', $image)) {
                    $postData['logo'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
            }
        }
        if($request->hasFile('about_company_image')){
            $original_filename = $request->file('about_company_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('about_company_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('about_company_image')->move('./public/upload/company/', $image)) {
                    $postData['about_company_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        if($request->hasFile('company_mission_image')){
            $original_filename = $request->file('company_mission_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_mission_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_mission_image')->move('./public/upload/company/', $image)) {
                    $postData['company_mission_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        if($request->hasFile('company_vision_image')){
            $original_filename = $request->file('company_vision_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_vision_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_vision_image')->move('./public/upload/company/', $image)) {
                    $postData['company_vision_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        $postData['slug'] = $input['company_name'];
        $postData['first_name'] = $input['first_name'];
        $postData['middle_name'] = $input['middle_name'];
        $postData['surname'] = $input['surname'];
        $postData['primary_email'] = $input['primary_email'];
        $postData['secondary_email'] = $input['secondary_email'];
        $postData['contact_1'] = $input['contact_1'];
        $postData['contact_2'] = $input['contact_2'];
        $postData['meta_keyword'] = $input['meta_keyword'];
        $postData['meta_title'] = $input['meta_title'];
        $postData['meta_description'] = $input['meta_description'];
        $postData['website'] = $input['website'];
        $postData['about_company'] = $input['about_company'];
        $postData['company_mission'] = $input['company_mission'];
        $postData['company_vision'] = $input['company_vision'];
        $postData['company_address'] = json_encode($input['company_address']);
        $postData['pan_no'] = $input['pan_no'];
        $postData['gst_no'] = $input['gst_no'];
        $postData['adhaar_no'] = $input['adhaar_no'];
        $postData['is_active'] = $input['is_active'];
        
        try {
            $checkpostData = DB::table('companies')->select('*')->where(['short_code'=>$input['short_code']])->count();
            if($checkpostData > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $res['response']['status'] = "success";
                $res['response']['message'] = "Company updated Successfully";
                /*$response = response()->json([
                    'status' =>  "success",
                    'message' => "Company updated Successfully",
                    'data' => $returnPostData],200);*/
            }else{
                $postData['created'] = date('Y-m-d H:i:s');
                $res['response']['status'] = "success";
                $res['response']['message'] = "Company created Successfully";
                /*$response = response()->json([
                    'status' =>  "success",
                    'message' => "Company created Successfully"
                ],200);*/
            }
            $res['company'] = Company::updateOrCreate(['short_code'=>$input['short_code']], $postData);
            $company['company_id'] = $res['company']->id;
            $company['user_id'] = $input['user_id'];
            $res['company_user'] = DB::table('user_company')->insert($company);
                if($res){
                    $response = response()->json([
                    $res['response'],
                    'data' => $res['company']],200);
                    //echo "<pre>";print_r($res);exit;
                    return $response;
                } else {
                    return response()->json(['status'=>'error','message'=>'Something Wrong!','data'=>$res],200);
                }
         }
         catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    // public function deleteCompany(Request $request, $id){
    //     //echo 'Testimonial Delete';exit; 
    //     $postData = $request->post();
    //     if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
    //         $is_active = $postData['is_active'];
    //     } else {
    //         $is_active = 0;
    //     }
    //     try{
    //         $res = $is_active == '1' ? 'Activate' : 'De-Activate';
    //         $delete = Company::where('id', $id)->update(['is_active' => $is_active]);
    //         //echo "<pre>"; print_r($delete);exit;
    //         if($delete){
    //              return response()->json(['status'=>'success','message'=>'Company '.$res.'d Successfully!']);
    //         } else {
    //             return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Company']);
    //         }
        
    //     } catch(\Illuminate\Database\QueryException  $e) {
    //         $error = explode(':',$e->getMessage());
    //         return response()->json(['status'=>'error','message'=>$error[1]]);
    //     } catch(Exception $ex) {
    //         return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
    //     }
    // }
    
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
          
            // 'slider_code' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
      
        $input = $request->all();
    
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
        } else {
            $postData['id'] = '';
        }
        $postData['company_name'] = $input['company_name'];
        if(isset($postData['short_code']) && (NULL !== $postData['short_code'])){
             $input['short_code'] = $postData['short_code'];
        } else {
            //$input['short_code'] = $this->generateSystemCode(isset($postData['company_name'])?$postData['company_name']:'TVS');
        }
        if($request->hasFile('logo')){
            $original_filename = $request->file('logo')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('logo')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('logo')->move('./public/upload/company/', $image)) {
                    $postData['logo'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
            }
        }
        if($request->hasFile('about_company_image')){
            $original_filename = $request->file('about_company_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('about_company_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('about_company_image')->move('./public/upload/company/', $image)) {
                    $postData['about_company_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        if($request->hasFile('company_mission_image')){
            $original_filename = $request->file('company_mission_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_mission_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_mission_image')->move('./public/upload/company/', $image)) {
                    $postData['company_mission_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        if($request->hasFile('company_vision_image')){
            $original_filename = $request->file('company_vision_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_vision_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_vision_image')->move('./public/upload/company/', $image)) {
                    $postData['company_vision_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        $postData['slug'] = $input['company_name'];
        $postData['first_name'] = $input['first_name'];
        $postData['middle_name'] = $input['middle_name'];
        $postData['surname'] = $input['surname'];
        $postData['primary_email'] = $input['primary_email'];
        $postData['secondary_email'] = $input['secondary_email'];
        $postData['contact_1'] = $input['contact_1'];
        $postData['contact_2'] = $input['contact_2'];
        $postData['meta_keyword'] = $input['meta_keyword'];
        $postData['meta_title'] = $input['meta_title'];
        $postData['meta_description'] = $input['meta_description'];
        $postData['website'] = $input['website'];
        $postData['about_company'] = $input['about_company'];
        $postData['company_mission'] = $input['company_mission'];
        $postData['company_vision'] = $input['company_vision'];
        $postData['company_address'] = $input['company_address'];
        $postData['pan_no'] = $input['pan_no'];
        $postData['gst_no'] = $input['gst_no'];
        $postData['adhaar_no'] = $input['adhaar_no'];
        $postData['is_active'] = $input['is_active'];
        
         try {
            $checkpostData = DB::table('companies')->select('*')->where(['short_code'=>$input['short_code']])->count();
            if($checkpostData > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $res['response']['status'] = "success";
                $res['response']['message'] = "Company updated Successfully";
                /*$response = response()->json([
                    'status' =>  "success",
                    'message' => "Company updated Successfully",
                    'data' => $returnPostData],200);*/
            }else{
                $postData['created'] = date('Y-m-d H:i:s');
                $res['response']['status'] = "success";
                $res['response']['message'] = "Company created Successfully";
                /*$response = response()->json([
                    'status' =>  "success",
                    'message' => "Company created Successfully"
                ],200);*/
            }
            $res['company'] = Company::updateOrCreate(['short_code'=>$input['short_code']], $postData);
            $company['company_id'] = $res['company']->id;
            $company['user_id'] = $input['user_id'];
            $res['company_user'] = DB::table('user_company')->insert($company);
                if($res){
                    $response = response()->json([$res['response'], 'data' => $res['company']],200);
                    //echo "<pre>";print_r($res);exit;
                    return $response;
                } else {
                    return response()->json(['status'=>'error','message'=>'Something Wrong!','data'=>$res],200);
                }
         }
         catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function assignUserCompanyRole(Request $request){
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
      
        $input = $request->all();
        $postData['role_id'] = $input['role_id'];
        $res = UserCompany::updateOrCreate(['user_id'=>$input['user_id'],'company_id'=>$input['company_id']], $postData);
        if($res){
            return response()->json([
                'status' =>  "success",
                'message' => "Role assigned to User in Company Successfully",
                'data' => $res],200);
        }else {
            return response()->json(['status'=>'error','message'=>'Something Wrong!','data'=>$res],200);
        }
    }
    
    /*public function createUpdateCompany(Request $request){
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'primary_email' => 'required',
            'contact_1' => 'required',
            'type' => 'required',
            // 'nature_of_business' => 'required',
            'company_address' => 'required',
            // 'website' => 'required',
            'domain' => 'required|alpha_num',
            'subdomain' => 'alpha_num',
            'about_company' => 'required',
            // 'pan_no' => 'required',
            // 'gst_no' => 'required',
            // 'adhaar_no' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        //$add = json_decode($input('company_address'), true);
        //return response()->json(['status'=>'error','message'=>$add],200);
        
        $postData['company_name'] = $input['company_name'];
        $postData['company_code'] = (isset($input['sub_domain']) && (NULL !== $input['sub_domain']))?str_replace('-', '',$input['sub_domain']):str_replace('-', '',$input['domain']);
        $postData['slug'] = Str::slug($input['company_name'], '-');
        $postData['first_name'] = $input['first_name'];
        $postData['middle_name'] = isset($input['middle_name'])?$input['middle_name']:NULL;
        $postData['surname'] = isset($input['surname'])?$input['surname']:NULL;
        $postData['primary_email'] = $input['primary_email'];
        $postData['secondary_email'] = isset($input['secondary_email'])?$input['secondary_email']:NULL;
        $postData['contact_1'] = $input['contact_1'];
        $postData['contact_2'] = isset($input['contact_2'])?$input['contact_2']:NULL;
        $postData['type'] = $input['type'];
        $postData['nature_of_business'] = isset($input['nature_of_business'])?$input['nature_of_business']:NULL;
        $postData['company_address'] = json_encode($input['company_address']);
        $postData['meta_keyword'] = isset($input['meta_keyword'])?$input['meta_keyword']:NULL;
        $postData['meta_title'] = isset($input['meta_title'])?$input['meta_title']:NULL;
        $postData['meta_description'] = isset($input['meta_description'])?$input['meta_description']:NULL;
        $postData['website'] = isset($input['website'])?$input['website']:' ';
        $postData['domain'] = isset($input['domain'])?$input['domain']:NULL;
        $postData['sub_domain'] = isset($input['sub_domain'])?str_replace('-', '',$input['sub_domain']):NULL;
        $postData['about_company'] = isset($input['about_company'])?$input['about_company']:NULL;
        $postData['company_mission'] = isset($input['company_mission'])?$input['company_mission']:NULL;
        $postData['company_vision'] = isset($input['company_vision'])?$input['company_vision']:NULL;
        $postData['pan_no'] = isset($input['pan_no'])?$input['pan_no']:NULL;
        $postData['gst_no'] = isset($input['gst_no'])?$input['gst_no']:NULL;
        $postData['adhaar_no'] = isset($input['adhaar_no'])?$input['adhaar_no']:NULL;
        
      
        if($request->hasFile('logo')){
            $original_filename = $request->file('logo')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('logo')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('logo')->move('./public/upload/company/', $image)) {
                    // if($request->file('logo')->move('./public/upload/setup/', $image)){
                        
                    // }
                    $postData['logo'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
            }
        }
        if($request->hasFile('about_company_image')){
            $original_filename = $request->file('about_company_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('about_company_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('about_company_image')->move('./public/upload/company/', $image)) {
                    $postData['about_company_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        if($request->hasFile('company_mission_image')){
            $original_filename = $request->file('company_mission_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_mission_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_mission_image')->move('./public/upload/company/', $image)) {
                    $postData['company_mission_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        if($request->hasFile('company_vision_image')){
            $original_filename = $request->file('company_vision_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_vision_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_vision_image')->move('./public/upload/company/', $image)) {
                    $postData['company_vision_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        
        try {
            $checkpostData = Company::where('website','LIKE',"%{$_SERVER['SERVER_NAME']}%")->where('is_active', true)->first();
            
            //echo $this->tbl_prefix($input['company_name']);exit;
            if(!empty($checkpostData) > 0){
                $postData['modified'] = date('Y-m-d H:i:s');
                $upd = Company::where('website','LIKE',"%{$_SERVER['SERVER_NAME']}%")->where('is_active', true)
                    ->update($postData);
                
                if($upd){
                    return response()->json(['status'=>'success', 'message'=>'Data updated successfully', 'data' => $postData],200);
                }else{
                    return response()->json(['status'=>'failed', 'message'=>'Failed to update data'],200);
                }
            }else{
                $postData['created'] = date('Y-m-d H:i:s');
                $postData['tbl_prefix'] = $this->tbl_prefix($input['company_name']); 
                $ins = DB::table('company')->insert($postData);
                if($ins){
                    $code = $postData['tbl_prefix'];
                    $productProductCategoryCreate = ProductProductCategory::create($code.'_product_product_categories');
                
                    $galleryCreate = Gallery::create($code.'_gallery');
                    $productCreate = Product::create($code.'_products');
                    $productCategoryCreate = ProductCategory::create($code.'_product_categories');
                    $productImageCreate = ProductImage::create($code.'_product_images');
                    $roleCreate = Role::create($code.'_roles');
                    $setpCreate = SetupConfig::create($code.'_setup');
                    $sliderCreate = Slider::create($code.'_sliders');
                    $sliderDetailCreate = SliderDetail::create($code.'_slider_details');
                    $testimonialCreate = Testimonial::create($code.'_testimonials');
                    $userCreate = User::create($code.'_users');
                    $enquiryCreate = Enquiry::create($code.'_enquiry');
                    $navigation = Navigation::create($code.'_enquiry');
                    
                    SetupConfig::insertDefault($postData);
                    return response()->json(['status'=>'success', 'message'=>'Company created successfully', 'data' => $postData],200);
                }else{
                    return response()->json(['status'=>'failed', 'message'=>'Failed to create company', 'data' => $postData],200);
                }
                
               
            }
            
            /*$res['company'] = Company::updateOrCreate(['company_code'=>$postData['company_code']], $postData);
       
            if($res['company']){
                if(isset($postData['created']) && (NULL !== $postData['created'])){
             // print_r($tableCreate);exit;
                    if(NULL !== $postData['domain']){
                        $code = $postData['domain'];
                    } else if(NULL !== $postData['sub_domain']){
                        $code = $postData['sub_domain'];
                    } else {
                        $code = $postData['company_code'];
                    }
                    
                    $productProductCategoryCreate = ProductProductCategory::create($code.'_product_product_categories');
                
                    $galleryCreate = Gallery::create($code.'_gallery');
                    $productCreate = Product::create($code.'_products');
                    $productCategoryCreate = ProductCategory::create($code.'_product_categories');
                    $productImageCreate = ProductImage::create($code.'_product_images');
                    $roleCreate = Role::create($code.'_roles');
                    $setpCreate = SetupConfig::create($code.'_setup');
                    $sliderCreate = Slider::create($code.'_sliders');
                    $sliderDetailCreate = SliderDetail::create($code.'_slider_details');
                    $testimonialCreate = Testimonial::create($code.'_testimonials');
                    $userCreate = User::create($code.'_users');
                    $enquiryCreate = Enquiry::create($code.'_enquiry');
                    $navigation = Navigation::create($code.'_enquiry');
                    
                    SetupConfig::insertDefault($postData);
                }
                if(isset($input['company_website']) && (NULL !== $input['company_website'])){
                    foreach($input['company_website'] as $key => $website){
                        
                        $company['company_id'] = $res['company']->id;
                        $company['company_code'] = $postData['company_code'];
                        $company['website_code'] = $this->generateCode('WEB');
                        $company['website_name'] = $website['website_name'];
                        $company['domain'] = $website['domain'];
                        $company['subdomain'] = $input['company_websites']['subdomain'];
                        $company['owner_name'] = $input['company_websites']['owner_name'];
                        $company['owner_primary_email'] = $input['company_websites']['owner_primary_email'];
                        $company['owner_secondary_email'] = $input['company_websites']['owner_secondary_email'];
                        $company['owner_contact_1'] = $input['company_websites']['owner_contact_1'];
                        $company['owner_contact_2'] = $input['company_websites']['owner_contact_2'];
                        $company['meta_title'] = $input['company_websites']['meta_title'];
                        $company['meta_description'] = $input['company_websites']['meta_description'];
                        $company['meta_keyword'] = $input['company_websites']['meta_keyword'];
                        $company['about_us'] = $input['company_websites']['about_us'];
                        $company['contact_us'] = $input['company_websites']['contact_us'];
        
                        if($request->hasFile('website_logo')){
                            $original_filename = $request->file('website_logo')->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $request->file('website_logo')->getMimeType();
                            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
                              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                                if ($request->file('website_logo')->move('./public/upload/company_website/', $image)) {
                                    $company['website_logo'] = $image;
                                } else {
                                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                                }
                            }else{
                            }
                        }
                    }

                    $res['company_website'] = DB::table('company_websites')->insert($company);
                        if($res['company_website']){
                            return response()->json(['status'=>'success', 'message'=>'Company & Website Created', 'data' => $res['company_websites']],200);
                        } else {
                            return response()->json(['status'=>'error','message'=>'Company Created But Unable to Create Website','data'=>$res['company']],200);
                        }
    
    
                } else {
                    return response()->json(['status'=>'success','message'=>'Company Created Successfully!','data'=>$res['company']],200);
                }
            } else {
                 return response()->json(['status'=>'error','message'=>'Company Not Created'],200);
            }*/
         /*  
        }
        catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>'Database Error Occurred', 'error'=>$e->getMessage()], 500);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()], 500);
        }
    }*/
    
    public function createUpdateCompany(Request $request){
        
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'primary_email' => 'required',
            'contact_1' => 'required',
            'type' => 'required',
            // 'nature_of_business' => 'required',
            'company_address' => 'required',
            // 'website' => 'required',
            'domain' => 'required|alpha_num',
            'subdomain' => 'alpha_num',
            'about_company' => 'required',
            // 'pan_no' => 'required',
            // 'gst_no' => 'required',
            // 'adhaar_no' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        //$add = json_decode($input('company_address'), true);
        //return response()->json(['status'=>'error','message'=>$add],200);
        
        $postData['company_name'] = $input['company_name'];
        $postData['company_code'] = (isset($input['sub_domain']) && (NULL !== $input['sub_domain']))?str_replace('-', '',$input['sub_domain']):str_replace('-', '',$input['domain']);
        $postData['slug'] = Str::slug($input['company_name'], '-');
        $postData['first_name'] = $input['first_name'];
        $postData['middle_name'] = isset($input['middle_name'])?$input['middle_name']:NULL;
        $postData['surname'] = isset($input['surname'])?$input['surname']:NULL;
        $postData['primary_email'] = $input['primary_email'];
        $postData['secondary_email'] = isset($input['secondary_email'])?$input['secondary_email']:NULL;
        $postData['contact_1'] = $input['contact_1'];
        $postData['contact_2'] = isset($input['contact_2'])?$input['contact_2']:NULL;
        $postData['type'] = $input['type'];
        $postData['nature_of_business'] = isset($input['nature_of_business'])?$input['nature_of_business']:NULL;
        $postData['company_address'] = json_encode($input['company_address']);
        $postData['meta_keyword'] = isset($input['meta_keyword'])?$input['meta_keyword']:NULL;
        $postData['meta_title'] = isset($input['meta_title'])?$input['meta_title']:NULL;
        $postData['meta_description'] = isset($input['meta_description'])?$input['meta_description']:NULL;
        $postData['website'] = isset($input['website'])?$input['website']:' ';
        $postData['domain'] = isset($input['domain'])?$input['domain']:NULL;
        $postData['sub_domain'] = isset($input['sub_domain'])?str_replace('-', '',$input['sub_domain']):NULL;
        $postData['about_company'] = isset($input['about_company'])?$input['about_company']:NULL;
        $postData['company_mission'] = isset($input['company_mission'])?$input['company_mission']:NULL;
        $postData['company_vision'] = isset($input['company_vision'])?$input['company_vision']:NULL;
        $postData['pan_no'] = isset($input['pan_no'])?$input['pan_no']:NULL;
        $postData['gst_no'] = isset($input['gst_no'])?$input['gst_no']:NULL;
        $postData['adhaar_no'] = isset($input['adhaar_no'])?$input['adhaar_no']:NULL;
        $postData['company_socialMedia'] = isset($input['company_socialMedia'])? json_encode($input['company_socialMedia'], TRUE):NULL;
      
        if($request->hasFile('logo')){
            //print_r($request->file('logo'));exit;
            $original_filename = $request->file('logo')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('logo')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
                $image2 = $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('logo')->move('./public/upload/company/', $image)) {
                    // if($request->file('logo')->move('./public/upload/setup/', $image)){
                        
                    // }
                    $postData['logo'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
                
                /*if((NULL!==$postData['sub_domain'] && !empty($postData['sub_domain']))){
                    if ($request->file('logo')->move('./public/upload/'.$postData['sub_domain'].'_files/profile_image/', $image2)) {
                        
                    } else {
                        return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    }
                }*/
                
            }else{
            }
        }
        if($request->hasFile('about_company_image')){
            $original_filename = $request->file('about_company_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('about_company_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('about_company_image')->move('./public/upload/company/', $image)) {
                    $postData['about_company_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        if($request->hasFile('company_mission_image')){
            $original_filename = $request->file('company_mission_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_mission_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_mission_image')->move('./public/upload/company/', $image)) {
                    $postData['company_mission_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        if($request->hasFile('company_vision_image')){
            $original_filename = $request->file('company_vision_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('company_vision_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('company_vision_image')->move('./public/upload/company/', $image)) {
                    $postData['company_vision_image'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }
        }
        /*return response()->json(['status'=>'success', 'message'=>'Company & Website Created', 'data' => $_SERVER['SERVER_NAME']],200);     
        echo json_encode([$request->getRequestUri()]);exit;*/
        try {
            //$checkpostData = Company::where('website','LIKE',"%{$_SERVER['SERVER_NAME']}%")->where('is_active', true)->first();
            
            //echo $this->tbl_prefix($input['company_name']);exit;
            //if(!empty($checkpostData) > 0){
            if(NULL!==$request->post('id')){
                $postData['modified'] = date('Y-m-d H:i:s');
                $upd = Company::where('id','=',$request->post('id'))->where('is_active', 1);
                
                    $upd->update($postData);
                    
                if($upd){
                    $companyDetail = $upd->first();
                    // $code = $postData['tbl_prefix'];
                    if(NULL !== $postData['nature_of_business']){
                        if(strtolower($postData['nature_of_business']) === 'education'){
                            QNAMgmtCreator::create($companyDetail->tbl_prefix.'_');
                        } else if(strtolower($postData['nature_of_business']) === 'logistic'){
                            DeliveryMgmtCreator::create($companyDetail->tbl_prefix.'_');
                        }
                    }
                    return response()->json(['status'=>'success', 'message'=>'Data updated successfully', 'data' => $postData],200);
                }else{
                    return response()->json(['status'=>'failed', 'message'=>'Failed to update data'],200);
                }
            }else{
                $postData['created'] = date('Y-m-d H:i:s');
                $postData['tbl_prefix'] = $this->tbl_prefix($input['company_name']); 
                $ins = DB::table('companies')->insert($postData);
                if($ins){
                    $code = $postData['tbl_prefix'];
                    if(NULL !== $postData['nature_of_business']){
                        if(strtolower($postData['nature_of_business']) === 'education'){
                            QNAMgmtCreator::create($code.'_');
                        } else if(strtolower($postData['nature_of_business']) === 'logistic'){
                            DeliveryMgmtCreator::create($code.'_');
                        }
                    }
                    $productProductCategoryCreate = ProductProductCategory::create($code.'_product_product_categories');
                
                    $roleCreate = Role::create($code.'_roles');
                    $userCreate = User::create($code.'_users');
                    $userRoleCreate = UserRoles::create($code.'_user_roles');
                    
                    
                    $galleryCreate = Gallery::create($code.'_gallery');
                    $productCreate = Product::create($code.'_products');
                    $productCategoryCreate = ProductCategory::create($code.'_product_categories');
                    $productImageCreate = ProductImage::create($code.'_product_images');
                    $setpCreate = SetupConfig::create($code.'_setup');
                    $sliderCreate = Slider::create($code.'_sliders');
                    $sliderDetailCreate = SliderDetail::create($code.'_slider_details');
                    $testimonialCreate = Testimonial::create($code.'_testimonials');
                    $enquiryCreate = Enquiry::create($code.'_enquiry');
                    $menu = Menu::create($code.'_menus');
                    $navigation = Navigation::create($code.'_navigation');
                    $companyBranch = Companybranch::create($code.'_company_branch');
                    
                    $blog = Blog::create($code.'_blogs');
                    $blogCategories = Blog::create($code.'_blog_categories');
                    $blogFiles = BlogFiles::create($code.'_blog_files');
                    $blogPosts = BlogPosts::create($code.'_blog_posts');
                    $blogsCategories = BlogsCategories::create($code.'_blogs_categories');
                    $teamCreate = Team::create($code.'_team');
                    $brandCreate = Brands::create($code.'_manufacturing_brands');
                    
                    if (Schema::hasTable($code.'_roles')) {
                        $roleData = array(
                            ["role_name"=> "SuperAdmin", "role_code"=>"SA", "slug"=>"superadmin", "created_at"=>date('Y-m-d H:i:s')],
                            ["role_name"=> "Admin", "role_code"=>"AD", "slug"=>"admin", "created_at"=>date('Y-m-d H:i:s')]
                            );
                     
                        DB::table($code.'_roles')->insert($roleData);
                    }
                    if (Schema::hasTable($code.'_users')) {
                    
                        $userData['name'] = $postData['first_name'].' '.$postData['surname'];
                        $userData['email'] = $postData['primary_email'];
                        $userData['password'] = Hash::make('U$er1234');
                        $userData['mobile'] = $postData['contact_1'];
                        $userData['address'] = $postData['company_address'];
                        $userData['created_at'] = date('Y-m-d H:i:s');
                        $userCreated = DB::table($code.'_users')->insert($userData);
                        if($userCreated){
                             if (Schema::hasTable($code.'_user_roles')) {
                                 $userRoleData['user_id'] = '1';
                                 $userRoleData['role_id'] = '1';
                                 $userRoleData['created_at'] = date('Y-m-d H:i:s');
                                 DB::table($code.'_user_roles')->insert($userRoleData);
                             }
                        }
                    }
                    //SetupConfig::insertDefault($postData);
                    return response()->json(['status'=>'success', 'message'=>'Company created successfully', 'data' => $postData],200);
                }else{
                    return response()->json(['status'=>'failed', 'message'=>'Failed to create company', 'data' => $postData],200);
                }
                
               
            }
            
            /*$res['company'] = Company::updateOrCreate(['company_code'=>$postData['company_code']], $postData);
       
            if($res['company']){
                if(isset($postData['created']) && (NULL !== $postData['created'])){
                // print_r($tableCreate);exit;
                    if(NULL !== $postData['domain']){
                        $code = $postData['domain'];
                    } else if(NULL !== $postData['sub_domain']){
                        $code = $postData['sub_domain'];
                    } else {
                        $code = $postData['company_code'];
                    }
                    
                    $productProductCategoryCreate = ProductProductCategory::create($code.'_product_product_categories');
                
                    $galleryCreate = Gallery::create($code.'_gallery');
                    $productCreate = Product::create($code.'_products');
                    $productCategoryCreate = ProductCategory::create($code.'_product_categories');
                    $productImageCreate = ProductImage::create($code.'_product_images');
                    $roleCreate = Role::create($code.'_roles');
                    $setpCreate = SetupConfig::create($code.'_setup');
                    $sliderCreate = Slider::create($code.'_sliders');
                    $sliderDetailCreate = SliderDetail::create($code.'_slider_details');
                    $testimonialCreate = Testimonial::create($code.'_testimonials');
                    $userCreate = User::create($code.'_users');
                    $enquiryCreate = Enquiry::create($code.'_enquiry');
                    $navigation = Navigation::create($code.'_enquiry');
                    
                    SetupConfig::insertDefault($postData);
                }
                if(isset($input['company_website']) && (NULL !== $input['company_website'])){
                    foreach($input['company_website'] as $key => $website){
                        
                        $company['company_id'] = $res['company']->id;
                        $company['company_code'] = $postData['company_code'];
                        $company['website_code'] = $this->generateCode('WEB');
                        $company['website_name'] = $website['website_name'];
                        $company['domain'] = $website['domain'];
                        $company['subdomain'] = $input['company_websites']['subdomain'];
                        $company['owner_name'] = $input['company_websites']['owner_name'];
                        $company['owner_primary_email'] = $input['company_websites']['owner_primary_email'];
                        $company['owner_secondary_email'] = $input['company_websites']['owner_secondary_email'];
                        $company['owner_contact_1'] = $input['company_websites']['owner_contact_1'];
                        $company['owner_contact_2'] = $input['company_websites']['owner_contact_2'];
                        $company['meta_title'] = $input['company_websites']['meta_title'];
                        $company['meta_description'] = $input['company_websites']['meta_description'];
                        $company['meta_keyword'] = $input['company_websites']['meta_keyword'];
                        $company['about_us'] = $input['company_websites']['about_us'];
                        $company['contact_us'] = $input['company_websites']['contact_us'];
        
                        if($request->hasFile('website_logo')){
                            $original_filename = $request->file('website_logo')->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $request->file('website_logo')->getMimeType();
                            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
                              $image = $original_filename_arr[0].time(). '.'.$file_ext;
                                if ($request->file('website_logo')->move('./public/upload/company_website/', $image)) {
                                    $company['website_logo'] = $image;
                                } else {
                                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                                }
                            }else{
                            }
                        }
                    }

                    $res['company_website'] = DB::table('company_websites')->insert($company);
                        if($res['company_website']){
                            return response()->json(['status'=>'success', 'message'=>'Company & Website Created', 'data' => $res['company_websites']],200);
                        } else {
                            return response()->json(['status'=>'error','message'=>'Company Created But Unable to Create Website','data'=>$res['company']],200);
                        }
    
    
                } else {
                    return response()->json(['status'=>'success','message'=>'Company Created Successfully!','data'=>$res['company']],200);
                }
            } else {
                 return response()->json(['status'=>'error','message'=>'Company Not Created'],200);
            }*/
           
        }
        catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>'Database Error Occurred', 'error'=>$e->getMessage()], 500);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()], 500);
        }
    }
    
    public function createCompanyWebsite(Request $request){
        $validator = Validator::make($request->all(), [
            'company_code' => 'required',
            'website_code' => 'required',
            'website_name' => 'required',
            'domain' => 'required',
            'subdomain' => 'required',
            'owner_name' => 'required',
            'owner_primary_email' => 'required',
            'owner_contact_1' => 'required',
            'about_us' => 'required',
            'contact_us' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
    
    
        $checkpostData = DB::table('companies')->select('id')->where(['company_code'=>$input['company_code']])->get()->first();
        if($checkpostData != null){
        
            $postData['company_id'] = $checkpostData['id'];
            $postData['company_code'] = $input['company_code'];
            $postData['website_code'] = $this->generateCode('WEB');
            $postData['website_name'] = $input['website_name'];
            $postData['domain'] = $input['domain'];
            $postData['subdomain'] = $input['subdomain'];
            $postData['owner_name'] = $input['owner_name'];
            $postData['owner_primary_email'] = $input['owner_primary_email'];
            $postData['owner_secondary_email'] = $input['owner_secondary_email'];
            $postData['owner_contact_1'] = $input['owner_contact_1'];
            $postData['owner_contact_2'] = $input['owner_contact_2'];
            $postData['meta_title'] = $input['meta_title'];
            $postData['meta_description'] = $input['meta_description'];
            $postData['meta_keyword'] = $input['meta_keyword'];
            $postData['about_us'] = $input['about_us'];
            $postData['contact_us'] = $input['contact_us'];
            $postData['created'] = date('Y-m-d H:i:s');
      

            if($request->hasFile('website_logo')){
                $original_filename = $request->file('website_logo')->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $request->file('website_logo')->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
                  $image = $original_filename_arr[0].time(). '.'.$file_ext;
                    if ($request->file('website_logo')->move('./public/upload/company_website/', $image)) {
                        $postData['website_logo'] = $image;
                    } else {
                        return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    }
                }else{
                }
            }
        
            try {
                $res['response']['status'] = "success";
                $res['response']['message'] = "Company created Successfully";
                $res['company_websites'] = DB::table('company_websites')->insert($postData);
            }
            catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
        }else{
             return json_encode(['message'=>'Please Select Company', 'status'=>'fail']); 
        }
    }
    
    public function tbl_prefix($companyName){
        $words = preg_split("/[\s,_-]+/", $companyName);
        $acronym = "";

        foreach ($words as $w) {
          $acronym .= mb_substr($w, 0, 1);
        }
        $acronym =  strtolower($acronym);
        
        $check = Company::where('tbl_prefix', 'Like', $acronym.'%')->count();
        $acronym = $acronym.$check;
        return $acronym;
    }
    
    public function deleteCompany(Request $request, $id){
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Vehicles::where('id', $id)->update(['is_active' => $is_active]);
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Company details '.$res.'d Successfully!'],200);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Company details'], 200);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function deleteCompanyWebsite(Request $request, $id){
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Vehicles::where('id', $id)->update(['is_active' => $is_active]);
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Company Website Details '.$res.'d Successfully!'],200);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Company Website Details'], 200);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }

    function generateCode($type = '') {
            $code = Helper::generateRandomString(6);
        
        return $type.'-'.$code;
    }
    
    public function CompanyTypes(Request $request){
         $postData = $request->all();
        try {
           $query = DB::table('company_types')->select(['company_types.*']);
            
            $companyTypes = $query->where('company_types.is_active', 'LIKE', '1')->get();
            
            
            if($companyTypes){
                return response()->json(['status'=>'success','data'=>$companyTypes]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1], 'db_error'=>$e->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function expose(Request $request){
        
        $user = Company::where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
        
        if($user){
            $user->logo = URL('/').'/public/upload/company/'.$user->logo;
            $user->about_company_image = URL('/').'/public/upload/company/'.$user->about_company_image;
            $user->company_mission_image = URL('/').'/public/upload/company/'.$user->company_mission_image;
            $user->company_vision_image = URL('/').'/public/upload/company/'.$user->company_vision_image;
            $user->company_address = json_decode($user->company_address, true);
            $chkTbl = Setup::checkCompanySetup($user->tbl_prefix);
                
            if(!empty($chkTbl)){
                $setup = Setup::getCompanySetup($user->tbl_prefix);
                //return response()->json(['status'=>'success','data'=>$config]);
                if($setup){
                    $config = json_decode($setup->config, true);
                    if($config['theme']=="dealer"){
                        $config['site_settings']['manufacturer_logo'] = (isset($config['site_settings']['manufacturer_logo']))?((strpos($config['site_settings']['manufacturer_logo'], 'http') === 0)?$config['site_settings']['manufacturer_logo']:URL('/').'/public/upload/'.$user->sub_domain.'_files/profile_image/'.$config['site_settings']['manufacturer_logo']):'';   
                    }else{
                        $config['site_settings']['manufacturer_logo'] = "";
                    }
                    $config['site_settings']['logo'] = (strpos($config['site_settings']['logo'], 'http') === 0)?$config['site_settings']['logo']:URL('/').'/public/upload/'.$user->sub_domain.'_files/profile_image/'.$config['site_settings']['logo'];
                    
                    $user->website_config = $config;
                }else{
                    $user->website_config = [];
                }
            }else{
                $user->website_config = [];
            }
            //return json_encode(['status'=>'success', 'data'=>$user[0]]);
            return response()->json(['status'=>'success','data'=>$user]);
        }else{
            return response()->json(['status'=>'error','message'=>"Invalid Request"]);
        }
    }
}
