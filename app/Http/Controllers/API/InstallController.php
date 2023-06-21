<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Helpers\Helper as Helper;
use DB;






class CompanyController extends BaseController
{
    private $pub_html;
    private $profileImagePath;
    public function __construct(){
        $this->profileImagePath = "./storage/app/public/profile_image/";
    }

    public function view(Request $request, $slug){
     
        $user = DB::table('companies')
        ->select(['*'])
        ->where('companies.slug','LIKE', $slug)->first();
        //print_r($user);exit;
        if($user){
            $user->logo = URL('/').'/public/upload/company/'.$user->logo;
            $user->about_company_image = URL('/').'/public/upload/company/'.$user->about_company_image;
            $user->company_mission_image = URL('/').'/public/upload/company/'.$user->company_mission_image;
            $user->company_vision_image = URL('/').'/public/upload/company/'.$user->company_vision_image;
            //return json_encode(['status'=>'success', 'data'=>$user[0]]);
               return response()->json(['status'=>'success','data'=>$user]);
        }
    }
    
    public function create_update(Request $request){
    
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
          
            // 'slider_code' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        $this->company =  Helper::getCompany();
        $input = $request->all();
        return $input;
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
    
    public function deleteCompany(Request $request, $id){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Company::where('id', $id)->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Company '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Company']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function addAddress($data = []){
         $validator = Validator::make($request->all(), [
            'address_1' => 'required',
            'site_name' => 'required',
            'user_id' => 'required',
            'city_id' => 'required',
            'state_id' => 'required',
            'pincode' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        
        $postData['slug'] = $input['company_name'];
        if(!empty($input['address']['area_id'])){
			$area['area_name'] = $post_data['data']['areas']['area_name'];
			$area['is_active'] = TRUE;
			$area['city_id'] = $post_data['data']['address']['city_id'];
			$area['created'] = date('Y-m-d H:i:s');
			$area['modified'] = date('Y-m-d H:i:s');
			$areaId = json_decode(Modules::run('areas/_register_admin_add', $area),true);
			$post_data['data']['address']['area_id'] = $areaId['id'];
			unset($post_data['data']['areas']['area_name']);

		}
    }
}
