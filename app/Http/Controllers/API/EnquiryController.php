<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Helpers\Helper as Helper;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Mail\EnquiryMail;

class EnquiryController extends Controller
{
    public function getEnquiryLists(Request $request){
       //echo 'Enquiry List';exit; 
       //echo "<pre>"; print_r($request->all());exit;
         $company = Helper::getCompany();
            /*$data = DB::table($company.'enquiry as e')
                ->where('e.is_active', 'LIKE', '1')
                ->get();*/
            $data = Enquiry::where('is_active', 'LIKE', '1')->get();
            $data['enquiry_type'] = Enquiry::select('enquiry_type')
                ->where('is_active', 'LIKE', '1')
                ->groupBy('enquiry_type')
                ->get();
               
             if($data){
                 //return $data;
                 foreach($data as $key => $value){
                    
                     if(isset($value->data) && (NULL !== $value->data)){
                        $data[$key]->data = json_decode($value->data);
                     }else{
                        $data[$key]->data = '';
                     }
                 }return $data;
                return response()->json(['status'=>'success','data'=>$data]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        try {

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getEnquiryList(Request $request){
        // $postData = $request->all();
        // print_r($postData);exit;
        $company = Helper::getCompany();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'enquiry as e')
            ->where('is_active', 'LIKE', '1');
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
            $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] : ""); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (e.enquiry_type like '%".$searchValue."%')";
            }
            
            if((isset($postData['enquiry_type'])) && (!empty($postData['enquiry_type']))){
                $query->where('e.enquiry_type','LIKE',$postData['enquiry_type']);
            }
            if((isset($postData['enquiry_type'])) && (!empty($postData['enquiry_type']))){
                $query->where('e.enquiry_type','LIKE',$postData['enquiry_type']);
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
        // echo $sql3->toSql();exit;
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
             if(isset($record->data) && (NULL !== $record->data)){
                $enquiry_data = json_decode($record->data);
             }else{
                $enquiry_data = [];
             }
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "email"=>$record->email,
            "phone"=>$record->phone,
            "data"=>$enquiry_data,
            "enquiry_type"=>$record->enquiry_type,
            "enquiry_code"=>$record->enquiry_code,
            "address"=>$record->address,
            "remark"=>$record->remark,
            "is_active"=>$record->is_active,
            "created"=>($record->created!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created)):'',
            "modified"=>($record->modified!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified)):'',
            'action'=>'Action'
           ); 
        }
        //return $data;
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
    
    public function createAndUpdateEnquiry(Request $request)
    {
        
       
        //echo "hello";exit;
        //echo  "<pre>";print_r($company);print_r($request->all());exit;
        $validate = [
            //'name' => 'required',
            'enquiry_type' => 'required'
        ];
        $validator = Validator::make($request->all(), $validate);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        if((NULL===$request->post('email') && NULL===$request->post('phone')) || (""===trim($request->post('email')) && ""===trim($request->post('phone')))){
            return response()->json(['status'=>'error','message'=>'Either Email or Phone is mandatory']);
        }
        
        $input = $request->all();
        
        // if(isset($input['address']) && (NULL !== $input['address'])){
        //     $input['address'] = $input['address'];
        // } else {
        //     $input['address'] = '';
        // }
        // if(isset($input['remark']) && (NULL !== $input['remark'])){
        //     $input['remark'] = $input['remark'];
        // } else {
        //     $input['remark'] = '';
        // }
        //     $input['data'] = json_encode($input, true);
        // if(isset($input['data']) && (NULL !== $input['data'])){
        // }
        // else{
        //     $input['data'] = '';
        // }
        $postData = [];
        if(isset($input['enquiry_code']) && (NULL !== $input['enquiry_code'])){
            $postData['enquiry_code'] = $input['enquiry_code'];
            $postData['status'] = isset($input['status'])?$input['status']:1;
            $postData['modified_by'] = auth()->id();
            $postData['modified'] = date('Y-m-d H:i:s');
            $response = 'Updated';
            $remark = Enquiry::select('*')->where('enquiry_code', $postData['enquiry_code'])->first();
            //$postRemarkHistory = E
            
            return response()->json(['status'=>'success','message'=>'Lead Created successfully','remark'=>$remark]);//echo  "<pre>";print_r($remark);exit;
            
        } else {
             $postData['enquiry_code'] = $input['phone'].time();
            $postData['created_by'] = (NULL !== auth()->id())?auth()->id():'1';
            $postData['created'] = date('Y-m-d H:i:s');
            $response = 'Created';
            
        }
        
       
        $postData['name'] = $input['name'];
        $postData['phone'] = (NULL!==$request->post('phone') && ""!==$request->post('phone'))?$input['phone']:"";;
        $postData['email'] = (NULL!==$request->post('email') && ""!==$request->post('email'))?$input['email']:"";
        $postData['enquiry_type'] = $input['enquiry_type'];
        $postData['remark'] = isset($input['remark'])?$input['remark']:NULL;
        $postData['address'] = isset($input['address'])?$input['address']:NULL;
        $postData['data'] = json_encode($input, true);
        $postData['is_active'] = isset($input['is_active'])?$input['is_active']:'1';
        //echo  "<pre>";print_r($postData);exit;
        $post = false;
        try {
            $post = Enquiry::updateOrCreate(['enquiry_code'=>$postData['enquiry_code']],$postData);
            return response()->json(['status'=>'success','message'=>'Lead Created successfully']);
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'errors','message'=>$error[1]]);
        } 
        
        
        
        try{
            if($post){
                //mail("primarykeytech@gmail.com","Test mail","This is to test mail");exit;
                $mail = 'Mr/Ms. '.$postData['name'].' <br> <p> Your Enquiry Has been Requested Successfully!</p>';
                if(isset($postData['remark']) && (NULL !== $postData['remark'])){
                    $mail .= '<p>Remark: '.$postData['remark'].' </p>';
                }
                $mail .= '<br/><br />';
                $companyData = json_decode(json_encode((new CompanyController)->view($request)), true);
                
                if($companyData['original']['status']==="success"){
                    $subject = 'Request Received';
            		$mail_type = 'html';
            		$from_email =  env('MAIL_FROM_ADDRESS');
            		$from_name = env('APP_NAME');
            		$to_email = $companyData['original']['data']['primary_email'];
            		$details = [
                        'title' =>'Enquiry Request',
                        'message' => 'Enquiry Request!',
                        'remark' => (isset($postData['remark']) ? $postData['remark'] : NULL),
                        'subject' => 'Enquiry Request',
                        'name' => $input['name'],
                        'message' => $mail
                    ];
                    
                    
                   /* Mail::send('enquiry.mail', $details, function($mail) use ($request->post('name'), $to_email) {
                    $message->to($to_email, $to_name)
                    ->subject(Laravel Test Mail’);
                    $message->from(‘SENDER_EMAIL_ADDRESS’,’Test Mail’);
                    });*/
                    
                    
                    /*echo $from_email.'<br>';
                    echo $to_email;*/
                    //print_r(new EnquiryMail($details));exit;
                    $mail = Mail::to($to_email)->send(new EnquiryMail($details));
                    print_r($mail);exit;
                    if(count(Mail::failures())>0) {
                        return response()->json([ 'status' =>  "success", 'message' => "Enquiry ".$response." Successfully But Mail Not Sent To Your Provided Mail",'data'=>$post]);
                    } else {
                        return response()->json([ 'status' =>  "success", 'message' => "Enquiry ".$response." Successfully And Mail Sent To Your Provided Mail",'data'=>$post]);
                    }
                    
                }else{
                    return response()->json(['status'=>'error','data'=>""]);
                }
                
                //$to_email = env()//$postData['email'];//$request->user()->email;
        		
        		

                

            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Enquiry.']);
            }
        }catch (\Swift_TransportException $Ste) {
             return response()->json(['status'=>'error','message'=>$Ste->getMessage()]);
        } catch(\Swift_RfcComplianceException $e) {
            return response()->json(['status'=>'error','message'=>"Address ".$to_email." seems invalid"]);
            echo "";
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function getEnquiryDetail(Request $request, $id){
        //echo 'Enquiry Detail';exit; 
        $enquiry = Enquiry::where('id',$id)->get();
        if($enquiry){
            return response()->json(['status'=>'success','message'=>'Enquiry Data', 'data'=>$enquiry]);
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Found']);
        }
    }
    
    public function deleteEnquiry(Request $request, $enquiry_code){
        //echo 'Enquiry Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Enquiry::where('enquiry_code', $enquiry_code)->update(['is_active' => $is_active]);
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Enquiry '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Enquiry']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    //update status
    public function updateEnquiryStatus(Request $request){
        //echo 'Enquiry Delete';exit; 
        $postData = $request->all();
        
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } 
        try{
            //$res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $status = Enquiry::where('id', $postData['id'])->update(['is_active' => $is_active]);
            if($status){
                 return response()->json(['status'=>'success','message'=>'Enquiry status updated Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to update Enquiry status']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function getExistingEnquiryList(Request $request){
        // $postData = $request->all();
        // print_r($postData);exit;
        $company = Helper::getCompany();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'enquiry as e')
            ->where('e.is_active', 'LIKE', '1');
        $searchQuery = '';
        // print_r($query->toSql());exit;
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
            $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] : ""); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (e.enquiry_type like '%".$searchValue."%')";
            }
            
            if((isset($postData['enquiry_type'])) && (!empty($postData['enquiry_type']))){
                $searchQuery->where('e.enquiry_type','LIKE',$postData['enquiry_type']);
            }
            if((isset($postData['status'])) && (!empty($postData['status']))){
                $query->where('e.status','LIKE',$postData['status']);
            }else{
                $query->whereIn('e.status',['pending','followup']);
            }
            if((isset($postData['data'])) && (!empty($postData['data']))){
                
                $query->where('e.data','LIKE','%'.$postData['data'].'%');
            }
            if((isset($postData['email'])) && (!empty($postData['email']))){
                
                $query->where('e.email', 'LIKE', $postData['email']);
                $query->where('e.data','LIKE','%email":"'.$postData['email'].'%');
            }
            if((isset($postData['phone'])) && (!empty($postData['phone']))){
                $query->where('e.phone', 'LIKE', $postData['phone']);
                $query->where('e.data','LIKE','%phone":"'.$postData['phone'].'%');
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
         //echo $sql3->toSql();exit;
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
             if(isset($record->data) && (NULL !== $record->data)){
                $enquiry_data = json_decode($record->data);
             }else{
                $enquiry_data = [];
             }
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "email"=>$record->email,
            "phone"=>$record->phone,
            "data"=>$enquiry_data,
            "enquiry_type"=>$record->enquiry_type,
            'status'=>Str::ucfirst($record->status),
            "address"=>$record->address,
            "remark"=>$record->remark,
            "is_active"=>$record->is_active,
            "created"=>($record->created!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created)):'',
            "modified"=>($record->modified!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified)):'',
            'action'=>'Action'
           ); 
        }
        //return $data;
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
    
}
