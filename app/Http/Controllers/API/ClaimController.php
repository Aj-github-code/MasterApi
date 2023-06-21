<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\claim;
use App\Models\UserEstimation;
use App\Models\ClaimInspectionImage;
use App\Models\ClaimAccidentImage;
use App\Models\Assessment;
use App\Models\product;
use App\Models\AssessmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Models\ProductCategories;

use App\Helpers\Helper as Helper;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $query = DB::table('claim')
            ->select(['claim.*', 'assessment.id as assessment_id', 'assessment.type as assessment_type'])
            ->where('claim.is_active', "1")
            ->join('assessment', 'assessment.claim_id', '=', 'claim.id', 'left');
        
        $role = $request->user()->roles()->get();
     
        $usersRole = $role[0]->id;
   
        if(isset($postData['type']) && ( NULL !== $postData['type'])){
            $query->where('assessment.type', 'LIKE', $postData['type']);
        } else {
            $query->where('assessment.type', 'LIKE', 'claim');
        }
        if(($usersRole != '1') && ($usersRole != '2')){
            // echo "hii";exit;
                
            $query->join('assign_claim', 'assign_claim.claim_id', '=', 'claim.id' )
            ->where('assign_claim.user_id', '=', auth()->user()->id )
             ->where('assign_claim.role_id', '=',  $role[0]->id );
            if(isset($postData['status']) && NULL !== $postData['status']){
                $query->where('assign_claim.status', '=',  $postData['status'] );
            
            }
                
        }
        
        
        
        
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
            $searchValue = (isset($postData['filter']) ? $postData['filter'] : ''); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (insurance_company like '%".$searchValue."%' or insured_name like '%".$searchValue."%' or claim_code like '%".$searchValue."%' or policy_id like '%".$searchValue."%')";
            }
        }
        
        $sql = $query;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
        $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
            
            $workshopDetails = DB::table('claim as c')
             ->select(['u.name', 'u.mobile', 'ac.created_at as workshopAssigned'])
            ->join('assign_claim as ac', 'ac.claim_id', '=', 'c.id')
            ->join('users as u', 'u.id', '=', 'ac.user_id')
            ->where('ac.role_id', '=', '9')
            ->where('c.claim_code', 'LIKE', $record->claim_code)
            ->whereNotIn('ac.status', [3])->orderBy('ac.id', 'desc')->first();
            if($workshopDetails){
                $workshop = $workshopDetails->name;
                 $workshopMobile = $workshopDetails->mobile;
                 $workshopAssigned = $workshopDetails->workshopAssigned;
            } else {
                $workshop = 'Not Assigned';
                $workshopMobile = NULL;
                $workshopAssigned = NULL;
            }
            $agentDetails = DB::table('claim as c')
            ->select(['u.name', 'u.mobile', 'ac.created_at as agentAssigned'])
            ->join('assign_claim as ac', 'ac.claim_id', '=', 'c.id')
            ->join('users as u', 'u.id', '=', 'ac.user_id')
            ->where('ac.role_id', '=', '8')
            ->where('c.claim_code', 'LIKE', $record->claim_code)
             ->whereNotIn('ac.status', [3])->orderBy('ac.id', 'desc')->first();
            if($agentDetails){
                $agent = $agentDetails->name;
                $agentMobile = $agentDetails->mobile;
                $agentAssigned = $agentDetails->agentAssigned;
            } else {
                $agent = 'Not Assigned';
                $agentMobile = NULL;
                $agentAssigned = NULL;
            }
            // echo '<pre>';print_r($record->insured_name);exit;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "claim_code"=>$record->claim_code,
            "policy_id"=>$record->policy_id,
            "policy_code"=>$record->policy_code,
            "assessment_id"=>$record->assessment_id,
            "type"=>$request->assessment_type,
            "insurance_company"=>$record->insurance_company,
            "insured_name"=>$record->insured_name,
            "insured_mobile_no"=>$record->insured_mobile_no,
            
            "workshop_name"=>$workshop,
             "workshop_mobile_no"=>$workshopMobile,
             "workshop_assigned"=>$workshopAssigned,
             
             "agent_name"=>$agent,
             "agent_mobile_no"=>$agentMobile,
             "agent_assigned"=>$agentAssigned,
             "vehicle_registration_no"=>$record->vehicle_registration_no,
             "place_of_accident"=>$record->place_of_accident,
            "policy_start_date"=>$record->policy_start_date,
            "policy_end_date"=>$record->policy_end_date,
            "vehicle_make"=>(NULL !== $record->vehicle_make)?$record->vehicle_make:'',
            "vehicle_model"=>(NULL !== $record->vehicle_model)?$record->vehicle_model:'',
            "vehicle_engine_no"=>(NULL !== $record->vehicle_engine_no)? $record->vehicle_engine_no:'',
            "vehicle_chassis_no"=>(NULL !== $record->vehicle_chassis_no)?$record->vehicle_chassis_no:'',
            "vehicle_odometer_reading"=>(NULL !== $record->vehicle_odometer_reading)?$record->vehicle_odometer_reading:'',
            "is_active"=>$record->is_active,
            // "assignment_date"=>(NULL !== $record->assignment_date)?date('d-M-Y', strtotime($record->assignment_date)):'',
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('Y-m-d H:i:s', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('Y-m-d H:i:s', strtotime($record->updated_at)):'',
            "status"=>$record->status,
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

    public function accidentImageList(Request $request)
    {
        if( (NULL===($request->post('claim_code')))){
            
            return response()->json(['status'=>'error','message'=>'Claim code not found']);
        }
         try {
            
            $users = DB::table('claim_accident as ca')->join('claim', 'claim.id', '=', 'ca.claim_id')
            ->select(['ca.image', 'ca.category', 'claim.claim_code'])
            ->where('ca.is_active', "1")->where('claim.claim_code', 'LIKE', $request->claim_code)->get();
             if(count($users)>0){
                foreach($users as $key=>$user){
                    if(NULL !== $user->image){
                        
                    $imgpath=URL('public/upload/'.$user->image);
                    $ext= pathinfo($imgpath, PATHINFO_EXTENSION);
                    // echo $ext;exit;
                    // echo "hiii";exit;
                    $imagedata = file_get_contents($imgpath);
                    // echo $imagedata;exit;
        
                   $users[$key]->image =  'data:image/' . $ext. ';base64,' . base64_encode($imagedata);
                    }
                    

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
        
        
        // $postData = $request->all();

        // $searchQuery = "";
        // $response = array();
        // $columnName = 'id';
        // $columnSortOrder = "desc";
        // $rowperpage = "-1";
        // $draw = '1';
        // $stalls = DB::table('claim_accident')
        //     ->select(['claim_accident.*'])
        //     ->where('claim_accident.is_active', "1");
        
        // $searchQuery = '';
        // // print_r($stalls->toSql());exit;
        // $searchQuery = ' 1 = 1';
        // if($_SERVER['REQUEST_METHOD']=='POST'){
        //     $postData = $request->post();
        //     //echo '<pre>';print_r($postData);exit;
        //     ## Read value
        //     $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
        //     $start = (isset($postData['start']) ? $postData['start'] : '0');
        //     $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
        //     $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
        //     $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
        //     $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
        //     $searchValue = (isset($postData['search']['value']) ? $postData['search']['value'] : ''); // Search value
         
        //     if($searchValue != ''){
        //       $searchQuery = " (t.claim_id like '%".$searchValue."%' or t.category like '%".$searchValue."%')";
        //     }
        // }
        
        // $sql = $stalls;
        // $records = $sql->count();
        // $totalRecords = $records;
        // // echo $totalRecords;exit;
        
        // $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
        // $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        // if ($rowperpage!='-1') {
        //     $sql3->offset($start)->limit($rowperpage);
        // }
        // $records = $sql3->get();
        // $data = array();
        // foreach($records as $recordKey => $record ){

        //     // $imgpath=URL('upload/'.$record->image);
        //     // $ext= pathinfo($imgpath, PATHINFO_EXTENSION);
        //     // // echo $ext;exit;
        //     // // echo "hiii";exit;
        //     // $imagedata = file_get_contents($imgpath);
        //     // echo $imagedata;exit;

        //     // $image = 'data:image/' . $ext. ';base64,' . base64_encode($imagedata);
    
        //     // echo '<pre>';print_r($record->insured_name);exit;
        //   $data[] = array(
        //     "sr_no" => $recordKey+1,
        //     "id"=>$record->id,
        //     "claim_id"=>$record->claim_id,
        //     "image"=>$record->image,
        //     "category"=>$record->category,
        //     "is_active"=>$record->is_active,
        //     "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
        //     "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
        //     "status"=>$record->status,
        //     'action'=>'Action'
        //   ); 
        // }
        // // echo "<pre>"; print_r($data);exit;
        // ## Response
        // $response = array(
        //   "draw" => intval($draw),
        //   "iTotalRecords" => $totalRecordwithFilter,
        //   "iTotalDisplayRecords" => $totalRecords,
        //   "aaData" => $data
        // );
        
        // return response()->json($response);
   
   
   
    }

    public function inspectionDetailList(Request $request)
    {
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $query = DB::table('claim_inspection')
            ->select(['claim_inspection.*'])
            ->where('claim_inspection.is_active', "1");
        
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
               $searchQuery = " (t.claim_id like '%".$searchValue."%' or t.category like '%".$searchValue."%')";
            }
            if(isset($postData['claim_code']) && NULL !== $postData['claim_code']){
                $query->join('claim', 'claim.id', '=', 'claim_inspection.claim_id')
               ->where('claim.claim_code', 'LIKE', $postData['claim_code']);
            }
        }
        
        $sql = $query;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
        $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){

            $imgpath=URL('public/upload/'.$record->image);
            $ext= pathinfo($imgpath, PATHINFO_EXTENSION);
            // echo $ext;exit;
            // echo "hiii";exit;
            $imagedata = file_get_contents($imgpath);
            // echo $imagedata;exit;

            $image = 'data:image/' . $ext. ';base64,' . base64_encode($imagedata);
    
            // echo '<pre>';print_r($image);exit;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "claim_id"=>$record->claim_id,
            "image"=>$image,
            "category"=>$record->category,
            "damage"=>$record->damage,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "status"=>$record->status,
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
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $postData = $request->all();
            
           
            if(isset($postData['policy_details']) && !empty($postData['policy_details'])){
                 $input = $postData['policy_details'];
                
            } else if(isset($postData['claim_details']) && !empty($postData['claim_details'])) {
                 $input = $postData['claim_details'];
            }  else if(isset($postData['policy_id']) && !empty($postData['policy_id'])){
                 $input['policy_id'] = $postData['policy_id'];
            }
            if(isset($postData['claim_code']) && !empty($postData['claim_code'])) {
                 $input['claim_code'] = $postData['claim_code'];
            }else{
                $input['claim_code'] = Str::random(12);
    
            }
        
            
            try {
                $post = claim::updateOrCreate(['claim_code'=>$input['claim_code']],$input);
    
                if($post){
                    return response()->json([
                        'status' => 'success',
                        'message' => "Claim Data Inserted Successfully",
                        'data' => ['claim_code'=>$input['claim_code']]
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
            // print_r($input);exit;
         }

        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAccidentImages(Request $request)
    {
        // echo "hello";exit;
        $input= new ClaimAccidentImage();

        
        // $images = [];
        $postData = $request->all('accident_details');
        
        // $images = $request->file('image');
        $inputs = [];
        
        // $inputs['claim_id'] = $request->claim_id; 
        // $inputs['image'] = $name;
        // $inputs['category'] = $request->category;
        // print_r($accident);
        if(isset($postData['accident_details']) && !empty($postData['accident_details'])){
            foreach($postData['accident_details'] as $key => $value){
                $claimId = DB::table('claim')->select('id')->where('claim_code','LIKE',$value['claim_code'])->first();
                // echo "hii";exit;
                
                $name = '';
                if(isset($value['image']) && NULL !== $value['image']){
                    $img = $value['image'];
                    // $img = str_replace('data:image/png;base64,', '', $img);
                    $image = explode(',', $img);
                
                    $img = str_replace(' ', '+', $image[1]);
                    $data = base64_decode($img);
                    $name =  date('Y').uniqid() .date('md'). '.png';
                    
                    $success = file_put_contents('public/upload/' .  $name, $data);
                }else{
                    $image = NULL;
                }
    
                $inputs['image'] = $name;
                $inputs['claim_id'] = $claimId->id; 
                $inputs['category'] = $value['category']; 
                // print_r($inputs);exit;
                $post = ClaimAccidentImage::updateOrCreate(['claim_id'=>$inputs['claim_id'],'category'=>$inputs['category']],$inputs);
                $inputs = [];
            }
        } else {

        }
       
        try {
      

            if($post){
                return response()->json([
                    'status' => 'success',
                    'message' => "Accident Image Inserted Successfully"
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

    public function storeInspectionDetails(Request $request)
    {
        // echo "hello";exit;
        $input= new ClaimInspectionImage();
        $postData = $request->all('inspection_details');
        $inputs = [];
        
        if(isset($postData['inspection_details']) && !empty($postData['inspection_details'])){
            foreach($postData['inspection_details'] as $key => $value){
                $claimId = DB::table('claim')->select('id')->where('claim_code','=',$value['claim_code'])->first();
                $name = '';
                if(isset($value['image']) && NULL !== $value['image']){
                    $img = $value['image'];
                    // $img = str_replace('data:image/png;base64,', '', $img);
                    $image = explode(',', $img);
                
                    $img = str_replace(' ', '+', $image[1]);
                    $data = base64_decode($img);
                    $name =  date('Y').uniqid() .date('md'). '.png';
                    
                    $success = file_put_contents('public/upload/' .  $name, $data);
            	 
                // 	$update[$key] = $file;
                    // $image = $value['image'];
                    // $name = Str::random(6).'.'.$image->getClientOriginalExtension();
                    // $path = public_path('upload');
                    // $image->move($path,$name);
                }else{
                    $image = NULL;
                }
    
                $inputs['image'] = $name;
                $inputs['claim_id'] = $claimId->id; 
                $inputs['category'] = $value['category']; 
                $inputs['damage'] = $value['damage'];

                // print_r($inputs);exit;
                $post = ClaimInspectionImage::updateOrCreate(['claim_id'=>$inputs['claim_id'],'category'=>$inputs['category']],$inputs);
                $inputs = [];
            }
        } else {

        }
       
        try {
            if($post){
                return response()->json([
                    'status' => 'success',
                    'message' => "Inspection Details Inserted Successfully"
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

    public function destroyClaim(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (!$input['is_active'])?0:1;
        
        $postData['updated_at'] = date('Y-m-d H:i:s');
        $claim = DB::table('claim')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($claim){
            $claim = DB::table('claim')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $msg
        ],200);
      
    }

    public function deleteAccidentImage(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (!$input['is_active'])?0:1;
        
        $postData['updated_at'] = date('Y-m-d H:i:s');
        $claim_accident = DB::table('claim_accident')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($claim_accident){
            $claim_accident = DB::table('claim_accident')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $msg
        ],200);
      
    }

    
    public function deleteInspectionDetail(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (!$input['is_active'])?0:1;
        
        $postData['updated_at'] = date('Y-m-d H:i:s');
        $claim_inspection = DB::table('claim_inspection')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($claim_inspection){
            $claim_inspection = DB::table('claim_inspection')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $msg
        ],200);
      
    }
    
    public function getClaimData(Request $request){
        
        // echo "hii";exit;
        
             
        try {
            $users = DB::table('claim')->select('claim.*')->where('claim.claim_code', 'LIKE', $request->claim_code)->first();

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
    
    public function getDashboardList(Request $request)
    {
        //   echo "hii";exit;
        
      $postData = $request->all();

        $validator = Validator::make($request->all(), [
                'filter' => 'required'
            ]);
       
            if($validator->fails()){
                return response()->json(['status'=>'error','message'=>$validator->errors()]);   
            }
     try {
            if($postData['filter'] == 'claim'){
                
             $newClaimQuery = DB::table('claim')
                ->select(["claim.status as status_id", 'claim_status.color_code', "claim_status.status_labal",DB::raw('COUNT(claim.status) as StatusCount')])
                ->join('claim_status', 'claim_status.id', '=', 'claim.status')
                ->where('claim_status.id', '=', '1');
                
                $role = $request->user()->roles()->get();
            //  echo $role;exit;
                $usersRole = $role[0]->id;
        
                if(($usersRole != '1') && ($usersRole != '2')){
                    // echo "hii";exit;
                    $newClaimQuery->join('assign_claim', 'assign_claim.claim_id', '=', 'claim.id')
                        ->where('assign_claim.user_id','=', auth()->user()->id)
                         ->where('assign_claim.status', '=', '1');
                        
                } else {
                    $newClaimQuery->where('claim_status.id', '=', '4');
                }
                $newClaim = $newClaimQuery->get();
                
                  $pendingClaimQuery = DB::table('claim')
                ->select(["claim.status as status_id",'claim_status.color_code', "claim_status.status_labal",DB::raw('COUNT(claim.status) as StatusCount')])
                ->join('claim_status', 'claim_status.id', '=', 'claim.status');
                if(($usersRole != '1') && ($usersRole != '2')){
                    // echo "hii";exit;
                    $pendingClaimQuery->join('assign_claim', 'assign_claim.claim_id', '=', 'claim.id')
                        ->where('assign_claim.user_id','=', auth()->user()->id)
                         ->where('assign_claim.status', '=', '2');
                } else {
                    $pendingClaimQuery->where('claim_status.id', '=', '2');
                }
                $pendingClaim = $pendingClaimQuery->get();
                
                 $rejectedClaimQuery = DB::table('claim')
                ->select(["claim.status as status_id", 'claim_status.color_code', "claim_status.status_labal",DB::raw('COUNT(claim.status) as StatusCount')])
                ->join('claim_status', 'claim_status.id', '=', 'claim.status');
                if(($usersRole != '1') && ($usersRole != '2')){
                    // echo "hii";exit;
                    $rejectedClaimQuery->join('assign_claim', 'assign_claim.claim_id', '=', 'claim.id')
                        ->where('assign_claim.user_id','=', auth()->user()->id)
                         ->where('assign_claim.status', '=', '3');
                } else {
                    $rejectedClaimQuery->where('claim_status.id', '=', '3');
                }
                $rejectedClaim =$rejectedClaimQuery->get();
                
                 $completedClaimQuery = DB::table('claim')
                ->select(["claim.status as status_id", 'claim_status.color_code', "claim_status.status_labal",DB::raw('COUNT(claim.status) as StatusCount')])
                ->join('claim_status', 'claim_status.id', '=', 'claim.status');
                if(($usersRole != '1') && ($usersRole != '2')){
                    // echo "hii";exit;
                     $completedClaimQuery->join('assign_claim', 'assign_claim.claim_id', '=', 'claim.id')
                        ->where('assign_claim.user_id','=', auth()->user()->id)
                        ->where('assign_claim.status', '=', '4');
                } else {
                    $completedClaimQuery->where('claim_status.id', '=', '4');
                }
                 $completedClaim =  $completedClaimQuery->get();
             
             
                $data['new_claim'] =  $newClaim; 
                $data['pending_claim'] =  $pendingClaim; 
                $data['rejected_claim'] =  $rejectedClaim; 
                $data['completed_claim'] =   $completedClaim; 
                
            }else if($postData['filter'] == 'user'){
                // echo "hii";exit;
                $query = DB::table('roles')
                    ->select(["roles.role_name as RoleName",DB::raw('COUNT(users.id) as Users')])
                    ->join('user_roles', 'user_roles.role_id', '=', 'roles.id')
                    ->join('users', 'users.id', '=', 'user_roles.user_id')
                    ->where('roles.is_active','=', '1');
                            
                $data =  $query->groupBy('roles.role_name')->get();
                // print_r($data);exit;
            }
                
            if($data){
                return response()->json(['status'=>'success','data'=>$data]);
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
    
     public function claimAssessment(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'claim_code' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        $postData['status'] = $input['status'];
        // print_r($input);exit;
        
        $postData['updated_at'] = date('Y-m-d H:i:s');
        $claim = DB::table('claim')->where('claim_code', $input['claim_code'])->update($postData);

        $msg = 'Some Error Occurred';
        if($claim){
            $claim = DB::table('claim')->where('claim_code', $input['claim_code'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $msg
        ],200);
      
    }
    
    
    public function Assessment(Request $request) {
        
        
        $postData = $request->all();
        // return response($postData);
        // $postData = json_decode(json_encode($request->all()), true);
        $validator = Validator::make($request->all(), [
            // 'status' => 'required',
            'claim_code' => 'required'
        ]);
        
        //   $data = $this->CreateAssessmentLog($postData['assessment']['id']);
        //      return response()->json(['status'=>'error','message'=>$data]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=> $validator->errors()->first()]);
        }
        
        if(isset($postData['assessment_id']) && (NULL !== $postData['assessment_id'])){
            $assessment['id'] = $postData['assessment_id'];
        } else {
            $assessment['claim_id'] = DB::table('claim')->select('id')->where('claim_code','=',$postData['claim_code'])->first()->id;
        }
        // $assessment['part_charges'] = $postData['assessment']['part_charges'];
        // $assessment['labour_charges'] = isset($postData['assessment']['labour_charges'])?$postData['assessment']['labour_charges']:'0.00';
        // $assessment['paint_charges'] = isset($postData['assessment']['paint_charges'])?$postData['assessment']['paint_charges']:'0.00';
        // $assessment['other_charges'] = isset($postData['assessment']['other_charges'])?$postData['assessment']['other_charges']:'0.00';
        // $assessment['grand_total'] = $postData['assessment']['grand_total'];
        if(isset($postData['assessment']['id']) && NULL !== $postData['assessment']['id']) {
            $assessment['id'] = $postData['assessment']['id'];
            $assessment['modified_at']  = date('Y-m-d H:i:s');
            $assessment['modified_by']  = auth()->user()->id;
              $data = $this->CreateAssessmentLog($request, $postData['assessment']['id']);
            //   return response()->json(['status'=>'error','message'=>$data]);
        } else {
            $assessment['id'] = '';
            $assessment['created_at']  =  $assessment['modified_at'] = date('Y-m-d H:i:s');
            $assessment['created_by']  = $assessment['modified_by'] =  auth()->user()->id;
            $assessment['assessment_code'] = $this->generateUniqueAssessmentCode();
            
        }
        if(isset($postData['assessment']['status']) && NULL !== $postData['assessment']['status']){
            $assessment['status'] = $postData['assessment']['status'];
        }
        if(isset($postData['assessment']['is_active']) && NULL !== $postData['assessment']['is_active']){
            $assessment['is_active'] = $postData['assessment']['is_active'];
        }
        
        // print_r($assessment);exit;
        $createAssessment = Assessment::updateOrCreate(['id'=> $assessment['id']],$assessment);
        $postData['assessment']['id'] = $createAssessment->id;
        if($createAssessment) {
            $assessmentDetail = $this->AssessmentDetail($postData);
            
            
          
             return response()->json(['status'=>'success','data'=>$createAssessment]);
        } else {
             return response()->json(['status'=>'error','message'=>'Something went wrong']);
        }
        
        
    }
    
    
    public function AssessmentDetail($postData = []) {
        
        
        if(isset($postData['assessment_detail']) && (NULL !== $postData['assessment_detail'])){
            
            foreach($postData['assessment_detail'] as $category => $batch){
                foreach($batch as $products => $product) {
                      $assessmentDetail['batch_code'] = $this->generateUniqueBatchCode();
                    foreach($product as $key => $value){
                        
                         $assessmentDetail['assessment_id'] = $postData['assessment']['id'];
                        $assessmentDetail['product_id'] = $value['product_id'];
                        $assessmentDetail['hsn_code'] = $value['hsn_code'];
                        $assessmentDetail['unit_price'] = $value['unit_price'];
                        $assessmentDetail['gst'] = $value['gst'];
                        $assessmentDetail['qty'] = $value['qty'];
                         $assessmentDetail['remark'] = $value['remark'];
                       
                        $assessmentDetail['amount_after_tax'] = isset($value['amount_after_tax'])?$value['amount_after_tax']:'0.00';
                        if(isset($value['is_active']) && NULL !== $value['is_active']){
                            $assessmentDetail['is_active'] = $value['is_active'];
                        }
                     if(isset($value['is_product']) && NULL !== $value['is_product']){
                            $assessmentDetail['is_product'] = $value['is_product'];
                        } else {
                            unset($assessmentDetail['is_product']);
                        }
                        if(isset($value['id']) && NULL !== $value['id']) {
                            $assessmentDetail['id'] = $value['id'];
                            $assessmentDetail['category_id'] = $value['category_id'];
                            $assessmentDetail['modified_at']  = date('Y-m-d H:i:s');
                            $assessmentDetail['modified_by']  = auth()->user()->id;
                        } else {
                            $assessmentDetail['id'] = '';
                            $assessmentDetail['category_id'] = $category;
                            $assessmentDetail['product_info'] = json_encode($value['product_info']);
                            $assessmentDetail['created_at']  = date('Y-m-d H:i:s');
                            $assessmentDetail['created_by']  = auth()->user()->id;
                          
                            
                        }
                        
                         $createAssessmentDetail = AssessmentDetail::updateOrCreate(['id' => $assessmentDetail['id']],$assessmentDetail);
                         if($createAssessmentDetail){
                            if($assessmentDetail['remark'] === 'open'){
                                $updateClaim = claim::where('claim_code', 'LIKE', $postData['claim_code'])->update(['allow_additional_claim' => '1']);
                            }
                             
                         }
                    }
                    
                }
            }
        }
    }
    
    
    
    public function updateAssessmentDetailById(Request $request){
        $postData = $request->all();
        
         $validator = Validator::make($request->all(), [
            // 'status' => 'required',
            'claim_code' => 'required',
            'assessment_id' => 'required',
            
        ]);
        
        //   $data = $this->CreateAssessmentLog($postData['assessment']['id']);
        //      return response()->json(['status'=>'error','message'=>$data]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=> $validator->errors()->first()]);
        }
        
        try{
            if(isset($postData['product']) && (NULL !== $postData['product'])){
               
                    $data = $this->CreateAssessmentLog($request, $postData['assessment_id']);
                foreach($postData['product'] as $value){
                    $assessmentDetail = AssessmentDetail::where('id', '=', $value['assessment_detail_id'])->first();
                    
                    $withoutGst = $value['amount_after_tax']*$assessmentDetail['gst']/100;
                    $update['unit_price'] = $withoutGst/$assessmentDetail['qty'];
                    $update['amount_after_tax'] = $value['amount_after_tax'];
                    $update['modified_at'] = date('Y-m-d H:i:s');
                    $update['modified_by'] = auth()->user()->id;
         
                    $updateAssessmentDetail = AssessmentDetail::where('id', '=', $assessmentDetail->id)->update($update);
                }
                if($updateAssessmentDetail){
                    return response()->json(['status'=>'success', 'message'=>'Assessment Detail Updated Successfully!']);
                } else {
                    return response()->json(['status'=>'error', 'message'=>'Failed Update! Assessment Detail.']);
                }
                
            }
        
        
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    
   
   public function getAssessmentDetailProduct(Request $request){
       
        $postData= $request->all();
       
         $validator = Validator::make($request->all(), [
            // 'status' => 'required',
            'assessment_id' => 'required',
        ]);

   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=> $validator->errors()]);
        }
        
        try{
            $assessmentProducts = DB::table('assessment')->join('assessment_details', 'assessment_id', '=', 'assessment.id')
                ->join('products', 'products.id', '=', 'assessment_details.product_id')
                
                ->select('assessment_details.*', 'products.product')
                ->where('assessment.id', '=', $postData['assessment_id'])
                // ->where('claim.claim_code' ,'LIKE' , $postData['claim_code'])
                ->where('is_product' ,'=' , '1')
                ->get();
            
            
            
            if($assessmentProducts){
                return response()->json(['status'=>'success', 'data'=>$assessmentProducts, 'message'=>'Assessment Product Details']);
            } else {
                return response()->json(['status'=>'error', 'message'=>'Failed Update! Assessment Detail.']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
   }
   
    public function getAssessmentDetails(Request $request) {
        
        $postData = $request->all();
        
        $data1 = DB::table('assessment as a')
                        ->join('assessment_details as ad', 'ad.assessment_id', '=', 'a.id')
                        ->join('claim as c', 'c.id', '=', 'a.claim_id')
                        ->join('product_categories as pc', 'pc.id', '=', 'ad.category_id')         
                        ->join('products as p', 'p.id', '=', 'ad.product_id')
                        ->where('a.id', '=', $postData['assessment_id'])
                        // ->where('c.claim_code', 'LIKE', $request->post('claim_code'))
                        ->select(['ad.*','pc.category_name', 'p.product'])->get();
                        
        $assessmentDetails = [];
        $count  = 0;
        foreach($data1 as $ckey => $category) {
            if((NULL !== $category->is_product) && ($category->is_product=== 0)){
                
                $assessmentDetails[$category->category_name][$category->batch_code][$count++] = $category;
            } else {
                
                $category_name = array_search($category->batch_code, $assessmentDetails, true);
               $assessmentDetails[$category_name][$category->batch_code][$count++] = $category;
            //   $assessmentDetails =  array_insert_after($category, $category->batch_code, $assessmentDetails);
            }
        }
        $query = DB::table('assessment as a')
                        ->join('claim as c', 'c.id', '=', 'a.claim_id')
                         ->where('a.id', '=', $postData['assessment_id']);
                        // ->where('c.claim_code', 'LIKE', $request->post('claim_code'));
                        // if(isset($postData['type']) && (NULL !== $postData['type'])){
                        //     $query->where('a.type' , 'LIKE', $postData['type']);
                        // } else {
                        //     $query->where('a.type', 'LIKE', 'claim');
                        // }
         $assessment = $query->select(['a.*',])->orderBy('a.created_at', 'DESC')->first();

        $results['assessment_details'] = $assessmentDetails;
        $results['assessment'] = $assessment;
        
        if($assessmentDetails && $assessment) {
            return response()->json(['status'=>'success','data'=>$results]);
            
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Available']);
        }

    }
    
    public function addAssessmentImage(Request $request){
        $postData = $request->all();
        
            $validator = Validator::make($request->all(), [
            // 'status' => 'required',
            'id' => 'required',
            'images' => 'required',
        ]);

   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=> $validator->errors()->first()]);
        }
       
        try{     
            $img = $postData['images'];
            $image = explode(',', $img);
        
            $img = str_replace(' ', '+', $image[1]);
            $data = base64_decode($img);
            $name =  date('Y').uniqid() .date('md'). '.png';
            
            $success = file_put_contents('public/upload/' .  $name, $data);
            
            
            $update =  AssessmentDetail::where('id', '=', $postData['id'])->update(['images'=> $name]);
            
            if($update) {
                return response()->json(['status'=>'success','message'=>'Image Uploaded Successfully']);
                
            } else {
                return response()->json(['status'=>'error','message'=>'Unable to Upload Images']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
            // $img = str_replace('data:image/png;base64,', '', $img);
        
        
        
    }
    
     public function CreateAssessmentLog(Request $request,$assessmentId){
        $checkUserEstimation = DB::table('user_estimations')->select('*')->where('assessment_id', '=', $assessmentId)
        ->orderBy('id', 'desc')->first();
    
    // return $checkUserEstimation;
        if($checkUserEstimation){
           
           if($checkUserEstimation->created_by !== "".auth()->user()->id.""){
          
            $data1 = DB::table('assessment as a')
                ->join('assessment_details as ad', 'ad.assessment_id', '=', 'a.id')
                ->join('product_categories as pc', 'pc.id', '=', 'ad.category_id')         
                ->join('products as p', 'p.id', '=', 'ad.product_id')
                    ->where('a.id', '=', $assessmentId)
                // ->where('c.claim_code', 'LIKE', $request->post('claim_code'))
                ->select(['ad.*','pc.category_name', 'p.product'])->get();
                   
                //   return $data1;   
                $assessmentDetails = [];
                $count  = 0;
                foreach($data1 as $ckey => $category) {
                    $assessmentDetails[$category->category_name][$category->batch_code][$count++] = $category;
                }
                
                $assessment = Assessment::where('id', '=', $assessmentId)
                        
                                ->select(['*',])->orderBy('created_at', 'DESC')->first();
        
         
                $results['assessment_details'] = $assessmentDetails;
                $results['assessment'] = $assessment;
        
             

                $userData = DB::table('user_roles')->where('user_id', '=', $assessment->created_by)->first();
                if( $userData){
                    $Insert['role_id'] = $userData->role_id;
                } else {
                    $Insert['role_id'] = 0;
                }
                $Insert['user_id'] = $assessment->created_by;
                $Insert['assessment_id'] = $assessmentId;
                $Insert['last_estimation'] = json_encode($results,true);
                $Insert['created']  =  $Insert['modified'] = date('Y-m-d H:i:s');
                $Insert['created_by']  = $Insert['modified_by'] =   auth()->user()->id;
                $insert = UserEstimation::insert($Insert);
               
           }
        }
       
    }
    
    public function AddImagesToClaimQuestion(Request $request){
        
         $validator = Validator::make($request->all(), [
            // 'status' => 'required',
            'claim_code' => 'required'
        ]);
        
        //   $data = $this->CreateAssessmentLog($postData['assessment']['id']);
        //      return response()->json(['status'=>'error','message'=>$data]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=> $validator->errors()->first()]);
        }
        $postData = $request->post();
        
        if($postData){
            foreach ($postData['image'] as $key => $value) {
                define('UPLOAD_DIR', 'public/images/');
                $img = $value;
                // $img = str_replace('data:image/png;base64,', '', $img);
                $image = explode(',', $img);
                
                $img = str_replace(' ', '+', $image[1]);
                $data = base64_decode($img);
                $file =  uniqid() . '.png';
                $filePath = 'public/uploads/images/';
                $success = file_put_contents(UPLOAD_DIR . $file, $data);
        	 
            	$update[$key] = $file;
                // $success = file_put_contents($file, $data);
                // $data1[] = $file;
            }
            $query = claim::where('claim_code', 'LIKE', $postData['claim_code'])->update($update);
            
            if($query){
                return response()->json(['status'=>'success','message'=>'File Uploaded']);
            } else {
                return response()->json(['status'=>'error','message'=>'File Unable to upload!']);
            }
        
        }
    }
    
    public static function generateUniqueAssessmentCode()
    {
        do {
            $code = Helper::generateRandomString(10);
        } while (Assessment::where("assessment_code", "=", $code)->first());
  
        return $code;
    }
     public static function generateUniqueBatchCode()
    {
        do {
            $code = Helper::generateRandomString(10);
        } while (AssessmentDetail::where("batch_code", "=", $code)->first());
  
        return $code;
    }
    
    public function getAssessmentDetailsNew(Request $request) {
        
        $postData = $request->all();
        
        $query = Assessment::find($postData['assessment_id']);
        $assessment = $query->first()->toArray();
        $assessmentDetailsQuery = $query->assessmentDetails();
        
        $assessmentDetails = $assessmentDetailsQuery->get()->toArray();
        //echo '<pre>';
        $response['assessment'] = $assessment;
        //print_r($assessment->re);
        //print_r($assessmentDetails);
        foreach($assessmentDetails as $aKey=>$detail){
             $category  = '';
            $product = product::find($detail['product_id']);
            $detail['product'] = $product->product;
            if($detail['is_product']){
                $category = ProductCategories::find($detail['category_id']);
           
                $detail['category'] = $category->category_name;
                $detail['product'] = $product->product;
                $detail['estimate_amt'] = ($detail['unit_price']*$detail['qty'])+(($detail['gst']/100.00)*($detail['unit_price']*$detail['qty']));
                $response['assessmentDetails'][$detail['batch_code']] = $detail;
                //$response[]
            }else{
                 
                
                $response['assessmentDetails'][$detail['batch_code']]['services'][] = $detail;
                
                $estimateAmt = isset($response['assessmentDetails'][$detail['batch_code']]['estimate_amt'])?$response['assessmentDetails'][$detail['batch_code']]['estimate_amt']:0;
                
                
                $response['assessmentDetails'][$detail['batch_code']]['estimate_amt'] = $estimateAmt+($detail['unit_price']*$detail['qty'])+(($detail['gst']/100.00)*($detail['unit_price']*$detail['qty']));
            }
            
            
            //print_r($detail);
        }
    
        
        
        if($response) {
            return response()->json(['status'=>'success','data'=>$response]);
            
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Available']);
        }

    }
}
