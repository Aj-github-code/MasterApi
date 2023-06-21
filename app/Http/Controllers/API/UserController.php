<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Traits\Permission;
use App\Models\Roles;
use App\Models\States;
use App\Models\City;
use App\Models\User;
use App\Models\Team;
use App\Models\tags;
use App\Models\UserRole;
use App\Models\AssignClaim;
use App\Models\ProductCategories;
use App\Models\SocietymgmtFlatDetail;
use App\Models\userReportingManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller as BaseController;
use App\Helpers\TableGenerator\UserRole as UserRoles;
use DB;
use Exception;
use Illuminate\Support\Str;
use App\Helpers\Helper as Helper;
use App\Helpers\Crypt as Crypt;
use App\Helpers\Api_log as Log;
use Validator;

class UserController extends BaseController
{
    use Permission;
    private $pub_html;
    private $profileImagePath;
    public function __construct(){
        $this->profileImagePath = "./storage/app/public/profile_image/";
        $this->VerifyUser();
    }

    public function index($type){
        /*$user = User::paginate(10);
        echo json_encode($user);exit;*/
        $company = Helper::getCompany();
        try {

            $users = User::join($company.'user_roles', 'user_roles.user_id', '=', 'users.id')
             ->leftJoin($company.'user_product_category as upc', 'upc.user_id', '=', 'users.id')
             ->join($company.'product_categories', 'product_categories.id', '=', 'upc.product_category_id')
             ->leftJoin($company.'tags as tg', 'tg.id', '=', 'users.tag')
             ->join($company.'roles', 'roles.id', '=', 'user_roles.role_id')
             ->select('users.*', 'roles.role_name','product_categories.category_name','tags.name')
             ->where('roles.slug', 'LIKE', $type)->get();
             if(count($users)>0){
                foreach($users as $key=>$user){
                    $users[$key]->profile_image_path = $this->profileImagePath;
                }
                return response()->json(['status'=>'success','data'=>$users]);
                //return json_encode(['status'=>'success', 'data'=>$user]);
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
    
    public function register(Request $request)
    {
        $company = Helper::getCompany();
        //echo json_encode($request->all());exit;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'mobile' => 'required',
            'c_password' => 'required|same:password',
            // 'role' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
   
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['referal_code'] = $this->generateUniqueNumber();
        try {

            $user = User::create($input);
            if($user){
               
                return response()->json(['status'=>'success','message'=>'User Created Successfully']);
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

    // public function login(Request $request) {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $rules = [
    //             'email'    => 'required|regex:/(.+)@(.+).(.+)/i'
    //         ];

    //         //echo $request->notEncrypted; exit;
    //         if(NULL !== $request->notEncrypted){
    //             $postData = $request->all();
    //             $rules = [
                
    //             'password' => [
    //                 'required',
    //                 'string',
    //             ],
    //         ];
    //         } else {
    //             $rules['password'] =  [
    //                 'required',
    //                 'string',
    //                 'min:8',             // must be at least 10 characters in length
    //                 'regex:/[a-z]/',      // must contain at least one lowercase letter
    //                 'regex:/[A-Z]/',      // must contain at least one uppercase letter
    //                 'regex:/[0-9]/',      // must contain at least one digit
    //                 'regex:/[@$!%*#?&]/', // must contain a special character
                
    //         ];
    //             $validator = \Validator::make( $request->all(), ['request'=> 'required'] );
    //             if ( $validator->fails() ) {
    //                  return response()->json(['status'=>'error', 'message'=>'Invalid Request!']);   
    //             }
                
    //             // $_token = [];
    //             // $_token = json_encode(base64_decode($request->_token));
    //             // $email = $postData['email'] = base64_decode($request->email);
    //             // $password = $postData['password'] = base64_decode($request->password);
                
    //             $decryptedRequest = Crypt::decrypt($request->post('request'));
    //             $postData = [];
    //             $postData = json_decode($decryptedRequest, true);
    //         }
     
            
            
    //         $validation = \Validator::make($postData, $rules );
            
    //         if ( $validation->fails() ) {
                
    //             return response()->json(['status'=>'error', 'message'=>$validation->errors()->all()]);
    //         }
            
    //         try{
                
    //             if(!User::where('email', 'LIKE', $postData['email'])->first()){
                    
    //                 return response()->json(['status'=>'error', 'message'=>'Email Not Yet Regsitered! Please Sign In']);   
    //             }
    //             //print_r($postData);
                
    //             if(!$token = Auth::attempt(['email' => $postData['email'], 'password' => $postData['password']])) {
    //                 return response()->json(['status'=>'error', 'message'=>'Incorrect username or password']);
    //             }
    //             $data['user_roles'] = $request->user()->roles()->get();
    //             $data['user_details'] = auth()->user();
    //             $res = [
    //               'access_token'=>$token,
    //                 'status'=>'success', 
    //                 'token_type'=>'bearer',
    //                 'expires_in'=>auth()->factory()->getTTL()*60*24,
    //                 'data'=> Crypt::encrypt(json_encode($data)), 
    //                 'message'=>'Access Authorised'];
    //             // return $this->respondWithToken($token);
    //             $logData = ['request'=>json_encode($request->all()), 'token'=>$token, 'response'=>json_encode($res), 'success'=>false, 'url'=>$request->fullUrl()];
    //             Log::createLog($logData);
    //             return response()->json($res);
    //         } catch(\Illuminate\Database\QueryException  $e) {
    //             $logData = ['request'=>json_encode($request->all()), 'response'=>json_encode($e->getMessage()), 'success'=>false, 'url'=>$request->fullUrl()];
    //             Log::createLog($logData);
                
    //             return response()->json(['status'=>'error','message'=>get_env("DB_ERROR")]);
    //         } catch(Exception $ex) {
    //             $logData = ['request'=>json_encode($request->all()), 'response'=>json_encode($e->getMessage()), 'success'=>false, 'url'=>$request->fullUrl()];
    //             Log::createLog($logData);
    //             return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
    //         }
    //     } else {
    //         return response()->json(['status'=>'error', 'message'=>'Unauthorised Access']);
    //     }
    // }
    
    
    public function login(Request $request) {
        $company = Helper::getCompany();
        //  $blogCategories = UserRoles::create($company.'user_roles');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rules = [
                'email'    => 'required|regex:/(.+)@(.+).(.+)/i'
            ];

            //echo $request->notEncrypted; exit;
            if(NULL !== $request->notEncrypted){
                $postData = $request->all();
                $rules = [
                
                'password' => [
                    'required',
                    'string',
                ],
            ];
            } else {
                $rules['password'] =  [
                    'required',
                    'string',
                    'min:8',             // must be at least 10 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                
            ];
                $validator = \Validator::make( $request->all(), ['request'=> 'required'] );
                if ( $validator->fails() ) {
                     return response()->json(['status'=>'error', 'message'=>'Invalid Request!']);   
                }
                
                // $_token = [];
                // $_token = json_encode(base64_decode($request->_token));
                // $email = $postData['email'] = base64_decode($request->email);
                // $password = $postData['password'] = base64_decode($request->password);
                
                $decryptedRequest = Crypt::decrypt($request->post('request'));
                $postData = [];
                $postData = json_decode($decryptedRequest, true);
            }
     
            
            
            $validation = \Validator::make($postData, $rules );
            
            if ( $validation->fails() ) {
                
                return response()->json(['status'=>'error', 'message'=>$validation->errors()->all()]);
            }
            
          

                $user = User::where('email', 'LIKE', $postData['email'])->first();
                
                if(!$user){
                    return response()->json(['status'=>'error', 'message'=>'Email Not Yet Regsitered! Please Sign In']);   
                }
                $lockOut = false;
                if($user->lock_out === '1'){
                    if(strtotime($user->lock_out_time) > (strtotime(date('Y-m-d H:i:s')))){
                    $lockOut = true;
                        $diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($user->lock_out_time));

                        $years = floor($diff / (365*60*60*24));
                        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
                        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                        $hours = floor((($diff - $years * 365*60*60*24 - $months*30*60*60*24) - $days*60*60*24)/ (60*60));
                        $minutes = floor(((($diff - $years * 365*60*60*24 - $months*30*60*60*24) - $days*60*60*24) - $hours*60*60)/ (60));
                        $seconds = floor((((($diff - $years * 365*60*60*24 - $months*30*60*60*24) - $days*60*60*24) - $hours*60*60) - $minutes*60));
                        
                        $time = strtotime($user->lock_out_time) - strtotime(date('Y-m-d H:i:s'));
                        return response()->json(['status'=>'error', 'message'=>'User has been blocked! Try Again '.(($hours>0)? $hours.' Hours ':'').$minutes .' Min '.(($seconds>0)? $seconds.' Sec':'').' Later']);  
                    }
                }
                //print_r($postData);
                
                if(!$token = Auth::attempt(['email' => $postData['email'], 'password' => $postData['password']])) {
                    $errorText = '';
                    if(!$lockOut){
                        
                        if($user->failed_login == 3){
                            $user->update(['lock_out_time'=> date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date('Y-m-d H:i:s')))), 'lock_out'=>1]);
                            $errorText = ' User has been Blocked for next 30 minutes';
                        } else {
                            $failedLogin = ($user->failed_login+1);
                             $user->update(['failed_login'=> $failedLogin,'lock_out_time'=> '', 'lock_out'=>0,]);
                        // $user->update([]);
                            $errorText = (3 - ($failedLogin)).' Attempts Left';
                     
                        }
                    }
                    return response()->json(['status'=>'error', 'message'=>'Incorrect username or password! '.$errorText]);
                } else {
                    $user->update(['lock_out_time'=> '', 'lock_out'=>0, 'failed_login'=>0]);
                    
                }

                $data['user_roles'] = $request->user()->roles()->get();
                $data['user_details'] = auth()->user();
                $res = [
                   'access_token'=>$token,
                    'status'=>'success', 
                    'token_type'=>'bearer',
                    'expires_in'=>auth()->factory()->getTTL()*60*24,
                    'data'=> Crypt::encrypt(json_encode($data)), 
                    'message'=>'Access Authorised'];
                // return $this->respondWithToken($token);
                $logData = ['request'=>json_encode($request->all()), 'token'=>$token, 'response'=>json_encode($res), 'success'=>false, 'url'=>$request->fullUrl()];
                Log::createLog($logData);
                return response()->json($res);
                  try{
            } catch(\Illuminate\Database\QueryException  $e) {
                $logData = ['request'=>json_encode($request->all()), 'response'=>json_encode($e->getMessage()), 'success'=>false, 'url'=>$request->fullUrl()];
                Log::createLog($logData);
                
                return response()->json(['status'=>'error','message'=>$e->getMessage()]);
            } catch(Exception $ex) {
                $logData = ['request'=>json_encode($request->all()), 'response'=>json_encode($e->getMessage()), 'success'=>false, 'url'=>$request->fullUrl()];
                Log::createLog($logData);
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
        } else {
            return response()->json(['status'=>'error', 'message'=>'Unauthorised Access']);
        }
    }

    protected function respondWithToken($token) {
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()->getTTL()*60*24,
        ]);
    }
    
    public function profile() {
      
        return response()->json(auth()->user());
    }

    public function refresh() {

        try {

            return $this->respondWithToken(auth()->refresh());
        } catch(\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }

    public function logout(Request $request) {
        auth()->logout();
        return response()->json(['status'=>'success','message'=>'User Successfully Logged Out']);
    }

    
    public function resetPassword(Request $request){
        
        // $decryptedRequest = [];
        $validator = \Validator::make( $request->all(), ['request'=> 'required'] );
        if ( $validator->fails() ) {
             return response()->json(['status'=>'error', 'message'=>'Invalid Request!']);   
        }
        
        $decryptedRequest = Crypt::decrypt($request->post('request'));
        $postData = [];
        $postData = json_decode($decryptedRequest, true);
        
            $rules = [
                'old_password' => ['required','string','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/'],
                'new_password' => ['required','string','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/'],
                'confirm_password' => ['required','string','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/','same:new_password'],
            ];
            

            $validation = \Validator::make($postData, $rules );
        
            if ( $validation->fails() ) {
              
                return response()->json(['status'=>'error', 'message'=>$validation->errors()->all()]);
            }
            
        
        if(isset($postData['old_password']) && (NULL !== $postData['old_password'])){
            if(Auth::attempt(['email' => auth()->user()->email, 'password' => $postData['old_password']])){
               if(isset($postData['new_password']) && isset($postData['confirm_password'])){
                   if($postData['new_password'] === $postData['confirm_password']){
                       $password = Hash::make($postData['new_password']);
                       $user = User::where('email', auth()->user()->email)->update(['password'=>$password]);
                       if($user){
                           return response()->json(['status'=>'success', 'message'=>'Password Reseted Successfully!']);
                       } else {
                           return response()->json(['status'=>'error', 'message'=>'Password Reset Un-Successfull!']);
                       }
                   } else {
                       return response()->json(['status'=>'error', 'message'=>'Passwords Not Matched!']);
                   }
               } else {
                   return response()->json(['status'=>'error', 'message'=>'New Password and Confirm Password required']);
               }
            } else {
                return response()->json(['status'=>'error', 'message'=>'In-Correct Current Password!']);
            }
        } else {
            return response()->json(['status'=>'error', 'message'=>'Password Not Found!']);
        }
            
    }

    public function store(Request $request)
    {
        // return $request->all();
        
     
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'email' => 'required|email|unique:users',
            'password' => 'required',
            // 'mobile' => 'required|unique:users',
            'c_password' => 'required|same:password',
            'role' => 'required',
            // 'address' => 'required',
            // 'state' => 'required',
            // 'city' => 'required',
            // 'pincode' => 'required',
        ]);
   
   
   
        // if(NULL !== $request->post('email')){
        //     $user = User::where('email', 'LIKE', $request->post('email'))->first();
        //     if($user){
        //         return response()->json(['status'=>'error', 'message'=>'User Already Exist']);
        //     }
        // }
        if($validator->fails()){
            $str ='';
                return response()->json(['status'=>'error','message'=>$validator->errors()->first()]);       
            foreach($validator->errors() as $key => $error){
            }
        }  
   
        $input = $request->all();
        $res = '';
        
          if(isset($input['password']) && (!empty($input['password']))){
            $input['password'] = Hash::make($input['password']);
          }
        // $input['referal_code'] = $this->generateUniqueNumber();
       
            if(isset($input['tag']) && (!empty($input['tag']))){
                $tag['name'] = $input['tag'];
                $tag['slug'] = Str::slug($input['tag']);
                $tag['is_active'] = true;
                $tag['created_at'] = $tag['modified_at'] = date('Y-m-d H:i:s');
                $utag = tags::updateOrCreate(['name'=>$input['tag']],$tag);
                if($utag){
                    $input['tag'] = $utag->id;
                }
            }
                //print_r($input);exit;
            $user = User::create($input);
        
            if($user){
                   if(NULL !== $request->flat_no){
                        $floorWing = explode("|", $request->flat_no);
                        if(count($floorWing)>0){
                            $society = SocietymgmtFlatDetail::where('wing', 'LIKE', $floorWing[0])->where('flat_no', 'LIKE', $floorWing[1])->first();
                            if($society){
                                $updateSociety['user_unique_code'] = 'qwertyuii';
                                $updateSociety['email_id'] = 'test@admin.com';
                                $updateSociety['contact_1'] = '8282828282';
                                SocietymgmtFlatDetail::where('id', '=', $society->id)->update($updateSociety);
                            }
                            
                        }
                    }
                if($input['role']){
                    $role = Roles::where('slug', 'LIKE', $input['role'])->first();
                    $userRole['user_id'] = $user->id;
                    $userRole['role_id'] = $role->id;
                    $userRole['is_active'] = true;
                    $userRole['created_at'] = $userRole['modified_at'] = date('Y-m-d H:i:s');
                    $urole = UserRole::insert($userRole);
                }
                if(isset($input['reportingManager']) && (!empty($input['reportingManager']))){
                    
                    $reportingmanager = User::where('id', 'LIKE', $input['reportingManager'])->first();
                    $userRM['user_id'] = $user->id;
                    $userRM['rm_id'] = $reportingmanager->id;
                    $userRM['is_active'] = true;
                    $userRM['created_at'] = $userRM['modified_at'] = date('Y-m-d H:i:s');
                    //print_r($userRM);exit;

                    $user_rm = userReportingManager::insert($userRM);
                }
         
                // if(isset($input['category']) && (!empty($input['category']))){
                //     $categoryList = ProductCategories::select('*')->where('id', 'LIKE', $input['category'])->first();
                //     $userCategory['user_id'] = $user->id;
                //     $userCategory['product_category_id'] = $input['category'];
                //     $userCategory['is_active'] = true;
                //     $userCategory['created_at'] = $userCategory['modified_at'] = date('Y-m-d H:i:s');
                //     //print_r($userRM);exit;

                //     $user_category = DB::table('user_product_category')->insert($userCategory);
                // }
                
                if($urole){
                    return response()->json(['status'=>'success','message'=>'User Created, Role Assigned!']);
                } else {
                   return response()->json(['status'=>'success','message'=>'User Created Successfully!']);
                } 
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
             try {
        } catch(\Illuminate\Database\QueryException  $e) {
       
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        // $success['token'] =  $user->createToken('MyApp')->accessToken;
        // $success['name'] =  $user->name;
        // 'data'=>$success,
        return response()->json(['status'=>'success', 'message'=>'User register successfully.']);
    }

    
    public function edit(Request $request, $id)
    {
        $company = Helper::getCompany();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required',
            //'role' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
   
        $input = $request->all();
        $postData['name'] = $input['name'];
        $postData['email'] = $input['email'];
        $postData['mobile'] = $input['mobile'];
        $postData['address'] = $input['address'];
        
        $postData['state'] = $input['state'];
        $postData['city'] = $input['city'];
        $postData['pincode'] = $input['pincode'];
    
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = $request->user()->id;
        //print_r($postData);exit;
        if (!empty($request->hasFile('profile_image'))){
            //echo "hiii";print_r($request->hasFile('pdf_file'));exit;
            $original_filename = $request->file('profile_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('profile_image')->getMimeType();
            //echo '<pre>';print_r($file_ext);exit;
            $path = $this->pub_html;
            //print_r($path);exit;
           // if($file_ext == 'pdf'){
           if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('profile_image')->move('./public/upload/profile', $image)) {
                    $postData['profile_image'] = $image;
                } else {
                   return response()->json(['message'=>'cannot upload file', 'status'=>'fail']); 
                }
            }else{
               return response()->json(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        
        // try {//print_r($input);exit;
        
            if(isset($input['role'])){
                if($input['role'] === 'admin'){
                    if(isset($input['tag']) && (!empty($input['tag']))){
                        $tag['name'] = $input['tag'];
                        $tag['slug'] = Str::slug($input['tag']);
                        $tag['is_active'] = true;
                        $tag['created_at'] = $tag['modified_at'] = date('Y-m-d H:i:s');
                        $utag = tags::updateOrCreate(['name'=>$input['tag']],$tag);
                        if($utag){
                            $postData['tag'] = $utag->id;
                        }
                    }
                }
            }
     
          
            // print_r($postData);
            $user = User::where('id', $id)->update($postData);
            //$user = User::where('id', $id)->update($postData);
            // print_r($user);exit;
            if($user){
                
                $role = Roles::where('slug', 'LIKE', $input['role'])->first();
                
                               
                if(isset($input['role'])){
                    if($input['role'] !== 'admin'){
                        if(isset($input['manager']) && (!empty($input['manager']))){
                            $reportingManager = User::select('*')->where('id', 'LIKE', $input['manager'])->first();
                            if($reportingManager){
                      
                                $checkUserRM = userReportingManager::select('*')->where(['user_id' => $id])->first();
                                if($checkUserRM){
                                    $userRM['modified_at'] = date('Y-m-d H:i:s');
                                    $userRM['rm_id'] = $reportingManager->id;
                                    $uRM = userReportingManager::where(['user_id' => $id])->update($userRM);
                                    // if($uRM){
                                    //      return response()->json(['status'=>'success','message'=>'User Profile Updated Successfully!']);
                                    // } else {
                                    //   return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                                    // }
                                }else{
                                    $userRM['user_id'] = $id;
                                    $userRM['rm_id'] = $reportingManager->id;
                                    $userRM['is_active'] = 1;
                                    $userRM['created_at'] = $userRM['modified_at'] = date('Y-m-d H:i:s');
                                    $uRM = userReportingManager::insert($userRM);
                                //   if($uRM){
                                //         return response()->json(['status'=>'success','message'=>'User Updated And Role, Reporting Manager Assigned!']);
                                //     } else {
                                //       return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                                //     }
                                }
                            }
                            
                        }
                        if(isset($input['category']) && (!empty($input['category']))){
                            
                            $category = DB::table($company.'user_product_category')->select('*')->where('user_id', '=', $id)->first();
                                $userCategory['user_id'] = $id;
                                $userCategory['product_category_id'] = $input['category'];
                                $userCategory['is_active'] = true;
                            if($category){
                                $userCategory['modified_at'] = date('Y-m-d H:i:s');
                                $ucategory = DB::table($company.'user_product_category')->where('user_id', '=', $id)->update($userCategory);
                            } else {
                                
                                $userCategory['created_at'] = $userCategory['modified_at'] = date('Y-m-d H:i:s');
                                //print_r($userRM);exit;
            
                                $ucategory = DB::table($company.'user_product_category')->insert($userCategory);
                                // $userCategory['user_id'] = $id;
                                // $userCategory['product_category_id'] = $input['category'];
                                // $userCategory['is_active'] = 1;
                            }
                        }
                    } 
                }
                            //   return response()->json([$ucategory,$uRM]);
                if($role){
                    $checkUserRole = UserRole::where(['user_id' => $id, 'role_id'=>$role->id])->first();
                    if($checkUserRole){
                        $userRole['modified_at'] = date('Y-m-d H:i:s');
                        $urole = UserRole::where(['user_id' => $id, 'role_id'=>$role->id])->update($userRole);
                        if($urole){
                             return response()->json(['status'=>'success','message'=>'User And Role Updated Successfully!']);
                        } else {
                           return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                        }
                    }else{
                        $userRole['user_id'] = $id;
                        $userRole['role_id'] = $role->id;
                        $userRole['is_active'] = true;
                        $userRole['created_at'] = $userRole['modified_at'] = date('Y-m-d H:i:s');
                        $urole = UserRole::insert($userRole);
                       if($urole){
                            return response()->json(['status'=>'success','message'=>'User Updated And Role Assigned!']);
                        } else {
                           return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                        }
                    }
                } else {
                    return response()->json(['status'=>'error','message'=>'No Such Role Found']);
                }
 
              
                if(isset($input['category']) && (!empty($input['category']))){
                    $categoryList = ProductCategories::select('*')->where('id', 'LIKE', $input['category'])->first();
                    if($categoryList){
                    
                        $checkUserCategory = DB::table($company.'user_product_category')->select('*')->where(['user_id' => $id, 'product_category_id'=>$reportingManager->id])->first();
                        if($checkUserCategory){
                            $userCategory['modified_at'] = date('Y-m-d H:i:s');
                            $uRM = DB::table($company.'user_product_category')->where(['user_id' => $id, 'product_category_id'=>$reportingManager->id])->update($userRM);
                            if($uRM){
                                 return response()->json(['status'=>'success','message'=>'User Profile Updated Successfully!']);
                            } else {
                              return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                            }
                        }else{
                            $userCategory['user_id'] = $id;
                            $userCategory['product_category_id'] = $categoryList->id;
                            $userCategory['is_active'] = true;
                            $userCategory['created_at'] = $userCategory['modified_at'] = date('Y-m-d H:i:s');
                            $uCategory = DB::table($company.'user_product_category')->insert($userCategory);
                          if($uCategory){
                                return response()->json(['status'=>'success','message'=>'User Profile Updated!']);
                            } else {
                              return response()->json(['status'=>'success','message'=>'User Updated Successfully!']);
                            }
                        }
                    } else {
                        return response()->json(['status'=>'error','message'=>'No Such Role Found']);
                    }
                }
                
                
            } else {
                 return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        try {
         } catch(\Illuminate\Database\QueryException  $e) {
       
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
       
        
    }
    
    public function myprofiles(Request $request){
       // echo "<pre>";print_r($request->all());exit;
       
       $company = Helper::getCompany();
        try{
            $user = User::leftJoin($company.'user_roles', 'user_roles.user_id','=','users.id')
            ->leftJoin($company.'roles', 'roles.id','=', 'user_roles.role_id')
            //->leftJoin('users as us', 'us.id','=', 'user_reporting_manager.rm_id')
            ->leftJoin('states as s', 's.id', '=', 'users.state')
            ->leftJoin('cities as c', 'c.id', '=', 'users.city')
            //->leftJoin('users as us', 'us.id', '=', 'users.id')
            ->select(['users.id','users.name', 'users.email', 'users.mobile', 'users.address', 'users.pincode', 'users.tag',
            //'us.name',
            's.state_name', 'c.city_name', 'users.profile_image', 'roles.role_name', 'roles.slug'])
            ->where('users.email', auth()->user()->email)->first();
            
            //print_r($user);exit;
            if($user){
                
                $rm = User::leftJoin($company.'user_reporting_manager as urm', 'urm.rm_id', '=', 'users.id')->first();
                $tag = tags::where('id','=', $user->tag)->first();
                if($rm){
                     $user->reportingManager = $rm->name;
                }
                if(!empty($rm) && !empty($tag)){
                    //print_r($rm->name);exit;
                    $user->reportingManager = $rm->name.' ('.$tag->name.')';
                }
                if($user->profile_image !== NULL){
                    $user->profile_image = URL('/public/upload/profile/'.$user->profile_image);
                }
                return response()->json(['status'=>'success', 'data'=>$user]);
            } else {
                return response()->json(['message'=>'User Not Found', 'status'=>'error']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function show($id){
       
        $company = Helper::getCompany();
        if($id === NULL){
           return response()->json(['status'=>'fail', 'message'=>'unauthorised access']);
        }

        try{
            $user = User::leftJoin($company.'user_roles', 'user_roles.user_id','=','users.id')
            ->leftJoin($company.'roles', 'roles.id','=', 'user_roles.role_id')
            ->select(['users.id','users.name', 'users.email', 'users.mobile', 'users.profile_image', 'roles.role_name'])
            ->where('users.id', $id)->first();
            
            // print_r($user);exit;
            if($user){
                $user->profile_image = env('FILE_URL').'profile_image/'.$user->profile_image;
                return response()->json(['status'=>'success', 'data'=>$user]);
            } else {
                return response()->json(['message'=>'User Not Found', 'status'=>'error']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }

    public function userDeleteRole(Request $request, $id){
        // echo "hii";exit;
        // return response()->json($id);
        $validator = Validator::make($request->all(), [
            'role' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
        $input = $request->all();
        try{
            $role = Roles::where('slug', 'LIKE', $input['role'])->first();
            if($role){
            
                $checkUserRole = UserRole::select('id')->where(['user_id' => $id, 'is_active' => 1, 'role_id'=>$role->id])->first();
                if($checkUserRole){
                    $userRole['is_active'] = 0;
                    $userRole['modified_at'] = date('Y-m-d H:i:s');
                    $urole = UserRole::where(['user_id' => $id, 'role_id'=>$role->id])->update($userRole);
                    if($urole){
                        return response()->json(['status'=>'success', 'message'=>'Role Assign to User']);
                    } else {
                        return response()->json(['status'=>'error', 'message'=>'Unable To Revoke Role']);
                    }
                    
                }else{
                    return response()->json(['status'=>'success', 'message'=>'Role Already Revoked']);
                }
            } else {
                return response()->json(['status'=>'success', 'message'=>'No Such Roles']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
       
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   
    }

    public function userAssignRole(Request $request, $id){
        // return response()->json($id);
        $validator = Validator::make($request->all(), [
            'role' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
        $input = $request->all();
        try{
            $role = Roles::where('slug', 'LIKE', $input['role'])->first();
            if($role){
            
                $checkUserRole = UserRole::select(['id','is_active'])->where(['user_id' => $id, 'role_id'=>$role->id])->first();
                if($checkUserRole){
                    if($checkUserRole->is_active == 1){
                        return response()->json(['status'=>'success', 'message'=>'This Role Is Already Assigned To User']);
                    } else {
                        $updateData['is_active'] = true;
                        $urole = UserRole::where('id', $checkUserRole->id)->update($updateData);
                        if($urole){
                            return response()->json(['status'=>'success', 'message'=>'Role Assigned To User']);
                        } else {
                            return response()->json(['status'=>'error', 'message'=>'Role Not Assigned']);
                        }
                    }
                }else{

                    $userRole['user_id'] = $id;
                    $userRole['role_id'] = $role->id;
                    $userRole['is_active'] = true;
                    $userRole['created_at'] = $userRole['modified_at'] = date('Y-m-d H:i:s');
                    $urole = UserRole::insert($userRole);
                    if($urole){
                        return response()->json(['status'=>'success', 'message'=>'Role Assigned To User']);
                    } else {
                        return response()->json(['status'=>'error', 'message'=>'Role Not Assigned']);
                    }
                }
            } else {
                return response()->json(['status'=>'success', 'message'=>'No Such Roles']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
        
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   
        
        // print_r($user);exit;
    }
    

    public function userRoles($id){
        // return response()->json($id);
        $company = Helper::getCompany();
        if($id === NULL){
           return response()->json(['status'=>'fail', 'message'=>'unauthorised access']);
        }

        try{
            $user = User::leftJoin($company.'user_roles', 'user_roles.user_id','=','users.id')
            ->leftJoin($company.'roles', 'roles.id','=', 'user_roles.role_id')
            ->select(['roles.role_name'])
            ->where('users.id', $id)->get();
            
            // print_r($user);exit;
            if(count($user)>0){
                return response()->json(['status'=>'success', 'data'=>$user]);
            } else {
                return response()->json(['status'=>'success', 'message'=>'No User Found With This Role']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function user_detail(Request $request){
        $company = Helper::getCompany();
        $id = $request->user()->id;
        //echo $id;
        if($id === NULL){
            return response()->json(['status'=>'fail', 'message'=>'unauthorised access']);
        }
        $user = User::join($company.'user_roles', 'user_roles.user_id','=','users.id', 'left')
        ->join($company.'roles', 'roles.id','=', 'user_roles.role_id', 'left')
        ->select(['users.id','users.name', 'users.email', 'users.mobile', 'users.profile_image', 'roles.role_name'])
        ->where('users.id', $id)->get();
        //print_r($user);exit;
        if(count($user)>0){
            $user[0]->profile_image = env('FILE_URL').'profile_image/'.$user[0]->profile_image;
            return response()->json(['status'=>'success', 'data'=>$user[0]]);
        }
    }
    
    public function list(Request $request){
        // $postData = $request->all();
        // print_r($postData);exit;
        $company = Helper::getCompany();
        $select = array();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = User::join($company.'user_roles', 'user_roles.user_id', '=', 'users.id', 'left')
            ->join($company.'roles', 'roles.id', '=', 'user_roles.role_id', 'left')
            ->join('states as s', 's.id', '=', 'users.state', 'left')
            ->join('cities as c', 'c.id', '=', 'users.city', 'left');
            // ->join('tags as t', 't.id', '=', 'users.tag', 'left');
            // ->join('user_product_category as upc', 'upc.user_id', '=', 'users.id');
            // if($request->role_name == 'posp'){
            // $query ->join('product_categories', 'product_categories.id', '=', 'upc.product_category_id')
            // ->join('tags as tg', 'tg.id', '=', 'users.tag');
            // $select1 = ['product_categories.category_name','tg.name as tag_name'];//->select(['product_categories.category_name','tg.name as tag_name']);
            // }
            //->join('user_reporting_manager as urm', 'urm.rm_id', '=', 'users.id')
            //->join('users as u_rm', 'u_rm.id', '=', 'urm.rm_id')
            //->where('roles.slug','LIKE', $type)
            //->where('roles.id', 3)
            // $select = ['users.*', 'roles.role_name', 'roles.slug', 't.name as tag', 'c.city_name', 's.state_name'];
            $select = ['users.*', 'roles.role_name', 'roles.slug', 'c.city_name', 's.state_name'];
            // $select[] = array_merge($select1, $select2);
            $query->select($select);
            //print_r($select);exit;
        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            // echo '<pre>';print_r($postData);exit;
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] :  (isset($postData['filter']) ? $postData['filter'] : '')); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (users.name like '%".$searchValue."%' or users.email like '%".$searchValue."%'  or users.mobile like '%".$searchValue."%')";
            }
            
            if((isset($postData['role_name'])) && (!empty($postData['role_name']))){
                $query->where('roles.slug','LIKE',$postData['role_name']);
            }
            
            if(isset($postData['state']) && (NULL !== $postData['state'])){
                $query->where('s.id','=',$postData['state']);
            }
            if(isset($postData['city']) && (NULL !== $postData['city'])){
                $query->where('c.id','=',$postData['city']);
            }
        }
        
        // echo $query;exit;
        $sql = $query;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
        $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        //  echo $sql3->toSql();exit;
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
            $tag = null;
            $manager = null;
            $category = null;
            
            if($request->role_name === 'posp'){
                $user = User::select(['users.name as manager','pc.category_name'])
                                ->join($company.'user_reporting_manager as ur', 'ur.rm_id', '=', 'users.id', 'left')
                                ->join($company.'user_product_category as upc', 'upc.user_id', '=', 'users.id', 'left')
                                ->join($company.'product_categories as pc', 'pc.id', '=', 'upc.product_category_id', 'left')
                                ->where('users.id', '=', $record->id)->first();
                if($user){
                    $manager = $user->manager;
                    $category = $user->category_name;
                }
                                // return response()->json([$user->manager, $record->id]);
            }
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "email"=>$record->email,
            "mobile"=>$record->mobile,
            "role"=>$record->role_name,
            'category'=>$category,
            'manager'=>$manager,
            // 'tag'=>$record->tag,
            "address"=>$record->address,
            "state"=>$record->state,
            'state_name'=>$record->state_name,
            "city"=>$record->city,
            "city_name"=>$record->city_name,
            "district"=>$record->district,
            "pincode"=>$record->pincode,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
            'action'=>'Action'
           ); 
        }
        //echo "<pre>"; print_r($data);exit;
        ## Response
        $response = array(
           "draw" => intval($draw),
           "iTotalRecords" => $totalRecordwithFilter,
           "iTotalDisplayRecords" => $totalRecords,
           "aaData" => $data
        );
        
        return response()->json($response);
        //exit;
    }
    
    
    public static function generateUniqueNumber()
    {
        do {
            $code = Helper::generateRandomString(6);
        } while (User::where("referal_code", "=", $code)->first());
  
        return $code;
    }

    public function roleCreate(Request $request)
    {
        // echo "hiii";exit;
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            "role_name" => "required|unique:users",
            "role_code" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $postData['role_name'] = $input['role_name'];
        $postData['role_code'] = $input['role_code'];
        $postData['slug'] = Str::slug($input['role_name']);
        $postData['created_at'] = date('Y-m-d H:i:s');
        // print_r($postData);exit;
        $post = Roles::create($postData);
        try {

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Role Created Successfully"
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

    public function rolesList(Request $request)
    {
        // echo "hii";exit;
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $stalls = Roles::where('roles.is_active', "1");
        
        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            //echo '<pre>';print_r($postData);exit;
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ? $postData['search']['value'] : ''); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (t.role_name like '%".$searchValue."%' or t.role_code like '%".$searchValue."%' or t.slug like '%".$searchValue."%')";
            }
        }
        
        $sql = $stalls;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
            // echo '<pre>';print_r($record->insured_name);exit;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "role_name"=>$record->role_name,
            "role_code"=>$record->role_code,
            "slug"=>$record->slug,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            'action'=>'Action'
           ); 
        }
        // echo "<pre>"; print_r($data);exit;
        ## Response
        $response = array(
           "draw" => intval($draw),
           "iTotalRecords" => $totalRecordwithFilter,
           "iTotalDisplayRecords" => $totalRecords,
           "aaData" => $data
        );
        
        return response()->json($response);
   
    }

    public function getAgentAndWorkshopList(Request $request){
        // if(NULL === $request->claim_code){
        //     return response()->json(['status'=>'error','message'=>'Something went wrong']);
        // }
        // echo "hii";exit;
        $company = Helper::getCompany();
        $postData = $request->all();
        try {

            if(isset($postData['role']) && NULL !== $postData['role']){
                // ->join('users', 'users.pincode', 'LIKE', 'claim.pincode')
               $users = User::join($company.'user_roles', 'user_roles.user_id', '=', 'users.id')
               ->join($company.'roles', 'roles.id', '=', 'user_roles.role_id')
                 ->select('users.id', 'users.name', 'users.email', 'users.mobile', 'users.address', 'users.district', 'users.lat', 'users.lng', 'user_roles.role_id','roles.role_name')
                 ->where('roles.slug', 'LIKE', $postData['role'])->GroupBy('users.id')->get();
                //  ->where('claim.claim_code', 'LIKE', $postData['claim_code'])
                //  print_r($agent);exit;
                 
                // $workshop = DB::table('claim')->join('users', 'users.pincode', 'LIKE', 'claim.pincode')
                // ->join('user_roles', 'user_roles.user_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'user_roles.role_id')
                // ->select('users.id','claim.pincode', 'users.name', 'users.email', 'users.mobile', 'users.address', 'users.district', 'users.lat', 'users.lng', 'user_roles.role_id','roles.role_name')
                // ->where('claim.claim_code', 'LIKE', $request->post('claim_code'))->where('roles.role_name', 'LIKE', 'workshop')->get();
                 
             
                // $data['workshop'] = $workshop;
                 if($users){
                   
                    return response()->json(['status'=>'success','data'=>$users]);
                }else{
                    return response()->json(['status'=>'error','message'=>'No Data Available']);
                }
            } else {
                  return response()->json(['status'=>'error','message'=>'Users Role Not Provided']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   
    }
    
    public function createAssignClaim(Request $request)
    {
        
        $input = $request->all();
        // echo "hii";exit;
        if(isset($request->claim_code) && (!empty($request->claim_code))){
            $claim_id = DB::table('claim')->select('claim.id')
            ->where('claim.claim_code', 'LIKE', $request->claim_code)->get();
            $postData['claim_id'] = $claim_id[0]->id;
        }else{
            $postData['claim_id'] = $input['claim_id'];
        }
        // echo $claim_id;exit;
        $validator = Validator::make($request->all(), [
            // 'claim_id' => 'required',
            'user_id' => 'required',
            'role_id' => 'required',
            
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
    
        
        
        $postData['user_id'] = $input['user_id'];
        $postData['role_id'] = $input['role_id'];
        
        // print_r($postData);exit;
        try {
            $post = AssignClaim::create($postData);

            if($post){
                return response()->json([
                    'status' => 'success',
                    'message' => "Assign Claim Successfully"
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
    
    public function updateAssignClaim(Request $request)
    {
        $input = $request->all();
        // echo "hii";exit;
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
            'status' => 'required',
            
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors()]);       
        }
    
        
        
        $postData['status'] = $input['status'];
        
        // print_r($postData);exit;
        try {
            $post = DB::table('assign_claim')->where(['user_id' => $input['user_id'], 'role_id' => $input['role_id']])->update($postData);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Assign Claim status updated Successfully"
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
    
    
    public function userAssignReportingManager(Request $request, $id){
        // return response()->json($id);
        $validator = Validator::make($request->all(), [
            'reportingManager' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
        $input = $request->all();
        try{
            $rManager = User::select('*')->where('id', 'LIKE', $input['reportingManager'])->first();
            if($rManager){
            
                $checkUserrManager = userReportingManager::select(['id','is_active'])->where(['user_id' => $id, 'rm_id'=>$rManager->id])->first();
                if($checkUserrManager){
                    if($checkUserrManager->is_active == 1){
                        return response()->json(['status'=>'success', 'message'=>'This Reporting Manager Is Already Assigned To User']);
                    } else {
                        $updateData['is_active'] = true;
                        $urmanager = userReportingManager::where('id', $checkUserrManager->id)->update($updateData);
                        if($urmanager){
                            return response()->json(['status'=>'success', 'message'=>'Reporting Manager Assigned To User']);
                        } else {
                            return response()->json(['status'=>'error', 'message'=>'Reporting Manager Not Assigned']);
                        }
                    }
                }else{

                    $userRManager['user_id'] = $id;
                    $userRManager['rm_id'] = $rManager->id;
                    $userRManager['is_active'] = true;
                    $userRManager['created_at'] = $userRManager['modified_at'] = date('Y-m-d H:i:s');
                    $urmanager = userReportingManager::insert($userRManager);
                    if($urmanager){
                        return response()->json(['status'=>'success', 'message'=>'Reporting Manager Assigned To User']);
                    } else {
                        return response()->json(['status'=>'error', 'message'=>'Reporting Manager Not Assigned']);
                    }
                }
            } else {
                return response()->json(['status'=>'success', 'message'=>'No Such Roles']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
        
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   
        
        // print_r($user);exit;
    }
    
    public function userAssignCategory(Request $request, $id){
        // return response()->json($id);
        $validator = Validator::make($request->all(), [
            'category' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);       
        }
        $input = $request->all();
        try{
            $category = ProductCategories::where('slug', 'LIKE', $input['category'])->first();
            if($category){
            
                $checkUserCategory = DB::table($company.'user_product_category')->select(['id','is_active'])->where(['user_id' => $id, 'product_category_id'=>$category->id])->first();
                if($checkUserCategory){
                    if($checkUserCategory->is_active == 1){
                        return response()->json(['status'=>'success', 'message'=>'This Catgeory Is Already Assigned To User']);
                    } else {
                        $updateData['is_active'] = true;
                        $uCategory = DB::table($company.'user_product_category')->where('id', $checkUserCategory->id)->update($updateData);
                        if($uCategory){
                            return response()->json(['status'=>'success', 'message'=>'Catgeory Assigned To User']);
                        } else {
                            return response()->json(['status'=>'error', 'message'=>'Catgeory Not Assigned']);
                        }
                    }
                }else{

                    $userCategory['user_id'] = $id;
                    $userCategory['product_category_id'] = $category->id;
                    $userCategory['is_active'] = true;
                    $userCategory['created_at'] = $userCategory['modified_at'] = date('Y-m-d H:i:s');
                    $uCategory = DB::table($company.'user_product_category')->insert($userCategory);
                    if($uCategory){
                        return response()->json(['status'=>'success', 'message'=>'Catgeory Assigned To User']);
                    } else {
                        return response()->json(['status'=>'error', 'message'=>'Catgeory Not Assigned']);
                    }
                }
            } else {
                return response()->json(['status'=>'success', 'message'=>'No Such Roles']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
        
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   
        
        // print_r($user);exit;
    }
    
    public function uploadUserExcel(Request $request){
        $company = Helper::getCompany();
        try{
            $postData = $request->all();
           $fname = $_FILES['excel_file']['name'];
            $chk_ext = explode('.',$fname);
            $filename = $_FILES['excel_file']['tmp_name'];
            if(end($chk_ext)=='xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            if(end($chk_ext)=='xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            if(end($chk_ext)=='csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }
            $reader->setReadDataOnly(true);
            $worksheetData = $reader->listWorksheetInfo($filename);
    
            $workSheets = [];
            $sheetName = $worksheetData[0]['worksheetName'];
            $reader->setLoadSheetsOnly($sheetName);
             $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $datasheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            $mainData = [];
            $count = 0;
            
            foreach($data as $key => $row){
                foreach($row as $rKey =>$column){
                    if($key>0) {
                      if(!empty($data[0][$rKey])){
                          
                            $columnName = strtolower(str_replace("*","",str_replace(" ","_", $data[0][$rKey]))); 
                        }
                        $mainData[$count][$columnName] = $column;
                    }
                }
                
                $count = $count +1;
            }
           
                
                $column = count($mainData[$key]);
                $statusCount = $column+2;
                $remarkCount = $column+1;
                $datasheet->setCellValueByColumnAndRow($statusCount, 1, 'Status');
                $datasheet->setCellValueByColumnAndRow($remarkCount, 1, 'Remark');
                
    //              $spreadsheet->getActiveSheet()->getStyle('A1:P1')->getFill()
    // ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    // ->getStartColor()->setARGB('f0f401');
            foreach($mainData as $key => $value){
                $remark = '';
                $status = 'PASS';
                $rem = [];
                $input = [];
                $input['name'] = $mainData[$key]['name'];
                $input['email'] = $mainData[$key]['email'];
                $input['mobile'] = $mainData[$key]['mobile'];
                $input['aadhar_no'] = $mainData[$key]['aadhar_no'];
                $input['pan_no'] = $mainData[$key]['pan_no'];
                $input['user_code'] = $mainData[$key]['ref_code'];
                $input['password'] =  Hash::make($mainData[$key]['password']);
                $input['gender'] = $mainData[$key]['gender'];
                $input['pincode'] = $mainData[$key]['pincode'];
                
                $input['state'] = States::where('state_name', 'LIKE', $mainData[$key]['state'])->first()->id;
                $input['city'] = City::where('city_name', 'LIKE', $mainData[$key]['city'])->first()->id;
                
                DB::beginTransaction();
                $user = User::updateOrCreate(['email'=>$input['email']], $input);
                if($user){
                    
                    if(isset($postData['role']) && (NULL !== $postData['role'])){
                        
                        $role = Roles::where('role_name', 'LIKE', $postData['role'])->first();
                        if($role){
                            $userRole['user_id'] = $user->id;
                            $userRole['role_id'] = $role->id;
                            $userRole['is_active'] = true;
                            $userRole['created_at'] = $userRole['modified_at'] = date('Y-m-d H:i:s');
                            // return response()->json($userRole);
                            $uRoles = UserRole::updateOrCreate(['user_id'=>$user->id], $userRole);
                        }
                    }
                   
                    if(isset($mainData[$key]['course']) && (NULL !== $mainData[$key]['course'])){
                        $category = ProductCategories::select('id')->where('category_name', 'LIKE', trim($mainData[$key]['course'], ' '))->first();
                        if($category){
                            $userCategory = DB::table($company.'user_product_category')->where('user_id', '=', $user->id)->first();
                            if($userCategory){
                                $uCat['product_category_id'] = $category->id;
                                $uCat['is_active'] = 1;
                                $uCat['modified_at'] = $uCat['created_at'] = date('Y-m-d H:i:s');
                                $userCat = DB::table($company.'user_product_category')->where('user_id', $user->id)->update($uCat);
                              
                            } else {
                                $uCat['user_id'] = $user->id;
                                $uCat['product_category_id'] = $category->id;
                                $uCat['is_active'] = 1;
                                $uCat['modified_at'] = $uCat['created_at'] = date('Y-m-d H:i:s');
                               $userCat = DB::table($company.'user_product_category')->insert($uCat);
                            }
                            if(!$userCat){
                                $rem[] = 'Course not assigned';
                            }
                        } else {
                            $rem[] = 'Course not Available';
                        }
                    }
                    
                    if(isset($mainData[$key]['manager_name']) && (NULL !== $mainData[$key]['manager_name'])){
                        
                        $manager = User::select('id')->where('name', 'LIKE', $mainData[$key]['manager_name'])->first();
                        if($manager){
                            $mangerRecord = userReportingManager::select('id')->where('user_id', '=', $user->id)->first();
                            if($mangerRecord){
                                $rManager['rm_id'] = $manager->id;
                                $rManager['is_active'] = 1; $rManager['modified_at'] = date('Y-m-d H:i:s');
                                $RMan = userReportingManager::where('user_id', '=', $user->id)->update($rManager);
                            } else {
                                
                                $rManager['user_id'] =  $user->id;
                                $rManager['rm_id'] = $manager->id;
                                $rManager['is_active'] = 1;
                                $rManager['created_at'] = $rManager['modified_at'] = date('Y-m-d H:i:s');
                                $RMan = userReportingManager::insert($rManager);
                            }
                            if(!$RMan){
                                 $rem[] = 'Mananger Not Assigned';
                            }
                        } else {
                                $rem[] = 'Mananger Not Found With Given Name';
                            
                        }
                    }
                    
                } else {
                    $rem[] = 'User Not Created';
                }
                
              
                
                $pRemark = $key+1;
                $pStatus = $key+1;
                if(count($rem)>0){
                     DB::rollback();
                    $status = 'FAIL';
                    $remark = implode(', ',$rem);
                    $spreadsheet->getActiveSheet()->getStyleByColumnAndRow($statusCount, $pStatus)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('d71d1d');
                } else {
                     DB::commit();
                    $remark = '';
                    $spreadsheet->getActiveSheet()->getStyleByColumnAndRow($statusCount, $pStatus)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('17de38');
                }
                
               $datasheet->setCellValueByColumnAndRow($statusCount, $pStatus, $status);
                $datasheet->setCellValueByColumnAndRow($remarkCount, $pRemark, $remark);
                
                // $datasheet->
            }
            $filename = 'user upload '.date('Y_m_d').'.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
              ob_end_clean();
            $writer->save(base_path($filename));
            // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
            
            $headers = [
                  'Content-Type' => 'application/xlsx',
               ];
               
               $message =  (count($mainData) - count($rem)).' Successfull And '.count($rem).' Unsuccessfull Entries';

            return response()->json(['status'=>'success','message'=> $message, 'data'=>(URL('/').'/'.$filename)]);
            return response()->json(['status'=>'success','message'=>'File Uploaded']);
        
        
        } catch(\Illuminate\Database\QueryException  $e) {
        
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
            return response()->json($mainData);
            
    }
    
    public function exportLmsSummary(Request $request){

        $company = Helper::getCompany();
        $role = 'POSP';
        
        $users = User::select(['users.*', 'pc.category_name as course', 'u.name as manager'])
                    ->join($company.'user_roles as ur', 'ur.user_id', '=', 'users.id', 'left')
                    ->join($company.'roles as r', 'r.id', '=', 'ur.role_id')
                    ->join($company.'user_product_category as upc', 'upc.user_id', '=', 'users.id', 'left')
                    ->join($company.'product_categories as pc', 'pc.id', '=', 'upc.product_category_id', 'left')
                    ->join($company.'user_reporting_manager as urm', 'urm.user_id', '=', 'users.id', 'left')
                    ->join($company.'users as u', 'u.id', '=', 'urm.rm_id', 'left')
                    ->where('r.role_name', 'LIKE', $role)
                    ->GroupBy(['users.id'])
                    ->get();
        
        // return response()->json([$users]);
        $filename = 'LMS Summary.xlsx';
        if($users){
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            
            $columns = ['Sr No', 'Name', 'Email', 'Mobile', 'POS Ref Code', 'Course', 
                        'Traning Completed (Hr)', 'Training Start Date', 'Training Completion Date', 
                        'Exam Completion Date', 'Gender', 'District', 'City', 'State', 'Manager Name', 
                        'Total Assign Module', "No. of Module's Completed", "No. of Module's in Progress", 
                        "No. of Module's Pending", "Source (BQP/POS/Employee)", "Assigned or Self Nominated"];
            
            $column = 1;
            // $sheet->setCellValueByColumnAndRow(1, 1, );
            foreach($columns as $field){
                $sheet->getStyleByColumnAndRow($column, 1, $field)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('595959');
                $sheet->getStyleByColumnAndRow($column, 1, $field)->getFont()->getColor()->setRGB('F7F0F0');
                // $sheet->getColumnDimension('A'.$column)->setAutoSize(true);
                for($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
                   $sheet->getColumnDimension($i)->setAutoSize(TRUE);
                }

                   $sheet->getStyleByColumnAndRow($column, 1, $field)->getFont()->setBold( true );
                $sheet->setCellValueByColumnAndRow($column++, 1, $field);
                // $column++;
            }
            
            $row = 2;
            foreach($users as $key => $user){
                $column = 1;
                $sheet->getStyleByColumnAndRow($column, $row, $field)->getFont()->getColor()->setRGB('040404');
                $sheet->getStyleByColumnAndRow($column, $row, $field)->getFont()->setBold( false );
                $trainingHour = 'None';
                $examCompletionDate = 'None';
                $trainingCompletionDate = 'None';
                $trainingStartDate = 'Not Assigned';
                $assignedBy = 'N/A';
                
                $sheet->setCellValueByColumnAndRow($column++, $row, $row); 
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->name); 
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->email); 
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->mobile); 
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->user_code); 
                
                if((NULL !== $user->course) && ($user->course !== '')){
                    $training = DB::table($company.'assign_campaign')
                                        ->join($company.'campaigns as c', 'c.campaign_code', 'LIKE', 'assign_campaign.campaign_code', 'left')
                                        ->where('c.other_parameter', 'LIKE', '%"category":"'.$user->course.'",%')
                                        ->where('user_id', '=', $user->id)->first();
                                        
                    if($training){
                        if($training->created_by === $user->id){
                            $assignedBy = 'Self Nominated';
                        } else {
                            $assgignedBy = 'Assigned';
                        }
                        $trainingStartDate = date('d-M-y', strtotime($training->created_at));
                        $exam = DB::table($company.'user_exam_results')
                                            ->where('status', 'LIKE', 'PASS')
                                            ->where('campaign_code', 'LIKE', $training->campaign_code)->first();
                        $trainingHour = $trainingCompletionDate = 'Training not ended yet';
                        if($exam){
                            $trainingHour = round((strtotime($time1) - strtotime($time2))/3600, 1);
                            $examCompletionDate = date('d-M-y', strtotime($exam->modified));
                            $trainingCompletionDate = date('d-M-y', strtotime($exam->created));
                        }
                    }
                    $sheet->setCellValueByColumnAndRow($column++, $row, $user->course); 
                } else {
                    $sheet->setCellValueByColumnAndRow($column++, $row, 'Not Assigned'); 
                }
                    $sheet->setCellValueByColumnAndRow($column++, $row, $trainingHour); 
                    $sheet->setCellValueByColumnAndRow($column++, $row, $trainingStartDate); 
                    $sheet->setCellValueByColumnAndRow($column++, $row, $trainingCompletionDate); 
                    $sheet->setCellValueByColumnAndRow($column++, $row, $examCompletionDate); 
                
                
                
                
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->gender); 
                $sheet->setCellValueByColumnAndRow($column++, $row, $user->pincode); 
                if((NULL !== $user->city) && ($user->city !== '')){
                    $city = City::where('id', '=', $user->city)->first();
                    if($city){
                        $sheet->setCellValueByColumnAndRow($column++, $row, $city->city_name); 
                    } else{
                        
                        $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                    }
                } else {
                     $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                }
                
                if((NULL !== $user->state) && ($user->state !== '')){
                    $state = States::where('id', '=', $user->state)->first();
                    if($state){
                        $sheet->setCellValueByColumnAndRow($column++, $row, $state->state_name); 
                    } else {
                         $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                    }
                } else {
                     $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                }
                
                if((NULL !== $user->manager) && ($user->manager !== '')){
                    $sheet->setCellValueByColumnAndRow($column++, $row, $user->manager);
                } else {
                    $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A');
                }
                
                
                $query = DB::table($company.'assign_campaign')->where('user_id', '=', $user->id);
                $sheet->setCellValueByColumnAndRow($column++, $row, $query->count()); 
                $query = DB::table($company.'assign_campaign')->where('user_id', '=', $user->id)->where('status','=', 1);
                $sheet->setCellValueByColumnAndRow($column++, $row, $query->count()); 
                $query = DB::table($company.'assign_campaign')->where('user_id', '=', $user->id)->where('status','=', 4);
                $sheet->setCellValueByColumnAndRow($column++, $row, $query->count()); 
                $query = DB::table($company.'assign_campaign')->where('user_id', '=', $user->id)->where('status','=', 2);
                $sheet->setCellValueByColumnAndRow($column++, $row, $query->count()); 
                
               
                if((NULL !== $user->tag) && ($user->tag !== '')){
                    $tag = tags::where('id', '=', $user->tag)->first();
                    if($tag){
                        $sheet->setCellValueByColumnAndRow($column++, $row, $tag->name); 
                    } else {
                         $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                    }
                } else {
                     $sheet->setCellValueByColumnAndRow($column++, $row, 'N/A'); 
                }
                $sheet->setCellValueByColumnAndRow($column++, $row, $assgignedBy); 
                
                // $course = 
                
                $row++;
                
             
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
              ob_end_clean();
            $writer->save(base_path($filename));
            // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
            
            $headers = [
                  'Content-Type' => 'application/xlsx',
               ];

            return response()->json(['status'=>'success','message'=>'Lms Summary Generated', 'data'=>['link'=>(URL('/').'/'.$filename)]]); 
        }
                    
        
        
    }
    
    public function createTeam(Request $request){
        $input = $request->all();
        $post = array();
        //return $input;
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
         foreach($input['team'] as $key => $value){
         
         //print_r($value['name']);
         if(isset($value['name']) && (NULL !== $value['name'])){
             $data[$key]['name'] = $value['name'];
         }
         if(isset($value['designation']) && (NULL !== $value['designation'])){
             $data[$key]['designation'] = $value['designation'];
         }
         if(isset($value['description']) && (NULL !== $value['description'])){
             $data[$key]['description'] = $value['description'];
         }
             //$data[$key]['id'] = isset($value['id'])?$value['id']:NULL;
             if(isset($key) && (NULL !== $key)){
                $data[$key]['id'] = $key;
                $data[$key]['modified_by'] = auth()->id();
                $data[$key]['modified_at'] = date('Y-m-d H:i:s');
                $response = 'updated';
            } else {
                $data[$key]['id'] = '';
                $data[$key]['created_by'] = auth()->id();
                $data[$key]['created_at'] = date('Y-m-d H:i:s');
                $response = 'created';
                
            }
         
             //$image = $value['profile_img'];
             //upload Image start
            //return $_FILES['team']['name'][$key]['profile_img'];
            //return $request->hasFile('team.'.$key.'.profile_img');
            
            if($request->hasFile('team.'.$key.'.profile_img')){
                $profile_images = $request->file('team.'.$key.'.profile_img');
                $original_filename = $profile_images->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $profile_images->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
              
                	$profile_img = $original_filename_arr[0].time(). '.'.$file_ext;
                    if ($profile_images->move('./public/upload/'.$companyName.'/team', $profile_img)) {
                        // if ($other_images->move('./public/upload/product/', $profile_img)) {
                        $data[$key]['profile_img'] = $profile_img;
                        
                    } else { $response['profile_img'][]['error'] = 'Image Not Saved'; }
                } else { $response['profile_img'][]['error'] = 'Enter Valid Format'; }
                    
                
            }
         
             
             $post[] = Team::updateOrCreate(['id'=>$data[$key]['id']],$data[$key]);
         }//return response()->json([ 'data' => $post]);
         try{
            //print_r($post);exit;
             //$post = Team::updateOrCreate(['id'=>$value['id']],$data);
             return response()->json([ 'status' => "success",'message' => "Team Member ".$response." Successfully",'data'=>$post]);
         }catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error[1]]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
         //return $data;
    }
    
     public function createTeams(Request $request){
        $input = $request->all();
        $post = array();
        //return $input;
        $data = array();
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        
        foreach($input as $key=>$value){
             
        
         //print_r($value['name']);
             $data[$key]['name'] = $value['name'];
             $data[$key]['designation'] = $value['designation'];
             $data[$key]['description'] = $value['description'];
             
             //$data[$key]['id'] = isset($value['id'])?$value['id']:NULL;
             if(isset($value['id']) && (NULL !== $value['id'])){
                $data[$key]['id'] = $input['id'];
                $data[$key]['modified_by'] = auth()->id();
                $data[$key]['modified_at'] = date('Y-m-d H:i:s');
                $response = 'updated';
            } else {
                $data[$key]['id'] = '';
                $data[$key]['created_by'] = auth()->id();
                $data[$key]['created_at'] = date('Y-m-d H:i:s');
                $response = 'created';
                
            }
        }
             //$image = $value['profile_img'];
             //upload Image start
            /*return $_FILES['team']['name'][$key]['profile_img'];
            return $request->File();*/
            if($request->hasFile('profile_img')){
                $files = $request->file('team');
                foreach($files as $profile_images){
                    $original_filename = $profile_images->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $profile_images->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
              
                	$profile_img = $original_filename_arr[0].time(). '.'.$file_ext;
                    if ($profile_images->move('./public/upload/'.$companyName.'/team', $profile_img)) {
                        // if ($other_images->move('./public/upload/product/', $other_image)) {
                        $data[$key]['profile_img'] = $profile_images;
                        
                    } else { $response['profile_img'][]['error'] = 'Image Not Saved'; }
                } else { $response['profile_img'][]['error'] = 'Enter Valid Format'; }
                    
                }
            }
             
             $post[] = Team::updateOrCreate(['id'=>$data[$key]['id']],$data);
         
         try{
             return $post;
            //print_r($post);exit;
             //$post = Team::updateOrCreate(['id'=>$value['id']],$data);
             return response()->json([ 'status' => "success",'message' => "Team Member ".$response." Successfully",'data'=>$post]);
         }catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error[1]]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
         //return $data;
    }
    
    
    public function listTeam(Request $request){
        // $postData = $request->all();
        // print_r($postData);exit;
        $select = array();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
            $company = Helper::getCompany(); 
        }
        $query = DB::table($company.'team')->where('is_active','=','1')->orderBy('priority', 'ASC');
            $query->select('*');
            //print_r($select);exit;
        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            // echo '<pre>';print_r($postData);exit;
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] :  (isset($postData['filter']) ? $postData['filter'] : '')); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (name like '%".$searchValue."%' or designation like '%".$searchValue."%')";
            }
        }
        
        // echo $query;exit;
        $sql = $query;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
        $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        //  echo $sql3->toSql();exit;
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
           
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "designation"=>$record->designation,
            "description"=>$record->description,
            "profile_img"=>URL('/').'/public/upload/'.$companyName.'team/'.$record->profile_img,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
            'action'=>'Action'
           ); 
        }
        //echo "<pre>"; print_r($data);exit;
        ## Response
        $response = array(
           "draw" => intval($draw),
           "iTotalRecords" => $totalRecordwithFilter,
           "iTotalDisplayRecords" => $totalRecords,
           "aaData" => $data
        );
        
        return response()->json($response);
        //exit;
    }
    
    public function deleteTeam(Request $request){
        //echo 'Blog Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Team::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Team Member'.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Team Member']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1],'data'=>DB::getQueryLog()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function userStore(Request $request)
    {
        //echo json_encode($request->all());exit;
        
            $company = Helper::getCompany();
            
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:'.$company.'users',
            'password' => 'required',
            'mobile' => 'required|unique:'.$company.'users',
            'c_password' => 'required|same:password',
        ]);
        if(NULL !== $request->post('email')){
            //return $company;
            $user = User::where('email', 'LIKE', $request->post('email'))->first();
            //return $user;
            if($user){
                return response()->json(['status'=>'error', 'message'=>'User Already Exist']);
            }
        }
        if($validator->fails()){
            $str ='';
                return response()->json(['status'=>'error','message'=>$validator->errors()->first()]);       
            foreach($validator->errors() as $key => $error){
            }
        }  
   
            $input = $request->all();
        $res = '';
        
          if(isset($input['password']) && (!empty($input['password']))){
            $input['password'] = Hash::make($input['password']);
          }
        try {
            //print_r($input);
            $inputData = array(
                'name' =>$input['name'],
                'email' => $input['email'],
                'mobile' => $input['mobile'],
                'password' => $input['password']
                );
            $user = User::insertGetId($inputData);
            //print_r($user);
        
            if($user){
                //if($input['role']){
                    //DB::enableQueryLog();
                    $role = Roles::where('slug', 'LIKE', 'member')->first();
                    //print_r(DB::getQueryLog());exit;
                    $userRole['user_id'] = $user;
                    $userRole['role_id'] = $role->id;
                    $userRole['is_active'] = true;
                    $userRole['created_at'] = $userRole['modified_at'] = date('Y-m-d H:i:s');
                    $urole = UserRole::insert($userRole);
                //}
              $userDetail = User::where('id',$user);
                if($urole){
                    return response()->json(['status'=>'success','message'=>'User Created, Role Assigned!', 'data' => Crypt::encrypt(json_encode($userDetail))]);
                } else {
                   return response()->json(['status'=>'success','message'=>'User Created Successfully!']);
                } 
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
       
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        // $success['token'] =  $user->createToken('MyApp')->accessToken;
        // $success['name'] =  $user->name;
        // 'data'=>$success,
        return response()->json(['status'=>'success', 'message'=>'User register successfully.']);
    }
}
