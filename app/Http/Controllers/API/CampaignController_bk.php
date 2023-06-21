<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Config;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use App\Models\Roles;
use App\Helpers\Helper as Helper;
use App\Models\Campaign;
use App\Models\CampaignQuestion;
use App\Models\CampaignAnswer;
use App\Http\Controllers\API\QNAController;
use App\Models\UserExamResult;
use App\Models\UserExams;
use App\Models\UploadData;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CampaignController extends Controller
{
    
    public function getCampaignByReferenceID(Request $request){
        
        $postData = $request->all();
        
         $validator = Validator::make($request->all(), [
            'reference_no' => 'required'
        ]);
    // return response()->json(['status'=>'success','data'=>$postData['reference_id']]);
        if($validator->fails()){
             return response()->json(['status'=>'error','message'=>$validator->errors()]);
   
        }
        
            $campaign = Campaign::where('other_parameter' , 'LIKE', '%"reference_no":"'.$postData['reference_no'].'"%')->orderBy('id', 'desc')->first();
            
        try{
            if($campaign){
                $campaign['other_parameter'] = json_decode( $campaign['other_parameter'], true);
                if(isset($campaign['other_parameter']['user_parameters']['video']) && (NULL !==  $campaign['other_parameter']['user_parameters']['video'] )){
                    
                   
                    // $campaign['video'] =  base64_encode(URL('uploads/videos/'.$campaign['other_parameter']['video']));
                     $campaign['video'] =  base64_encode(URL('uploads/videos/'.$campaign['other_parameter']['user_parameters']['video']));
                }
                return response()->json(['status'=>'success','data'=>$campaign]);
            }else{
                return response()->json(['status'=>'error','message'=>'No data found']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function getCampaignByCampaignCode(Request $request){
        
        $postData = $request->all();
        
         $validator = Validator::make($request->all(), [
            'campaign_code' => 'required'
        ]);
            // return response()->json(['status'=>'success','data'=>$postData['reference_id']]);
        if($validator->fails()){
             return response()->json(['status'=>'error','message'=>$validator->errors()]);
   
        }
        
            $campaign = Campaign::where('campaign_code' , 'LIKE', $postData['campaign_code'])->orderBy('id', 'desc')->first();
            
        try{
            if($campaign){
                $campaign['other_parameter'] = json_decode( $campaign['other_parameter'], true);
                if(isset($campaign['other_parameter']['user_parameters']['video']) && (NULL !==  $campaign['other_parameter']['user_parameters']['video'] )){
                    
                   
                    // $campaign['video'] =  base64_encode(URL('uploads/videos/'.$campaign['other_parameter']['video']));
                     $campaign['video'] =  base64_encode(URL('uploads/videos/'.$campaign['other_parameter']['user_parameters']['video']));
                }
                return response()->json(['status'=>'success','data'=>$campaign]);
            }else{
                return response()->json(['status'=>'error','message'=>'No data found']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function getCampaign(Request $request){
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $user_role = $request->user()->roles()->get();
        
        $query = Campaign::from('campaigns as c')
            ->where('c.is_active', '1')
            ->where('c.other_parameter', 'LIKE', '%roles":{%'.$user_role[0]->slug.'%');
                    
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
            $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] : ""); // Search value
         
            if($searchValue != ''){
               $searchQuery = '(c.campaign_code LIKE "%'.$searchValue.'%" or c.other_parameter LIKE `%"reference_no":"'.$searchValue.'%"` or c.other_parameter  like `%"title":"'.$searchValue.'%"`)';
            }
            //print_r($request->post('campign_type'));exit;
            if(NULL!==$request->post('campaign_type')){
                $query->where('c.campaign_type', 'LIKE', $request->post('campaign_type'));
            }
            
            $query->join('assign_campaign as ac', 'ac.campaign_code', 'LIKE', 'c.campaign_code', 'left')->
                join('users as au', 'au.id', '=', 'ac.user_id', 'left')->
                join('user_roles as aur', 'aur.user_id', '=', 'au.id', 'left')->
                join('roles as ar', 'ar.id', '=', 'aur.role_id', 'left')->
                select(['c.*', 'ac.user_id', 'ac.created_by as assigned_by', 'ac.status', 'ar.role_name', 'au.name as assigned_name']);
            if(isset($postData['assigned']) && (NULL !== $postData['assigned'])){
                if($postData['assigned'] === 'all'){
                    
                } else if($postData['assigned'] === "self"){
                    $query->where('ac.created_by', auth()->id());
                } else if($postData['assigned'] === "admin"){
                    $query->where('ac.created_by', '!=', auth()->id());
                }
            }
           
            if(isset($postData['search']) && (NULL !== $postData['search'])){
               $query->where('c.other_parameter', "LIKE", '%"title":"'.$postData['search'].'%');
            }
        }
        
        $query->GroupBy('c.campaign_code');
        
        $sql = $query;
        //print_r($sql);exit;
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
        $data = [];
        //echo '<pre>';print_r($records);exit;
        foreach($records as $keys => $row){
            $video = '';
            $temp = [];
            $temp['other_parameter'] = json_decode( $row->other_parameter, true);
            // dd($temp['other_parameter']['user_parameters']['video'][0]["file"]);
            if(isset($temp['other_parameter']['user_parameters']['video'][0]["file"]) && (NULL !==  $temp['other_parameter']['user_parameters']['video'][0]["file"] )){
                 //$video =  base64_encode(URL('uploads/videos/'.$temp['other_parameter']['user_parameters']['video']));
                $video =  URL('uploads/campaign/videos/'.$temp['other_parameter']['user_parameters']['video'][0]["file"]);
            } else {
                 $video =  URL('uploads/videos/'.$temp['other_parameter']['user_parameters']['video']);
            }
            $user_prev_exam = UserExamResult::where('user_id' , 'LIKE', auth()->id())->orderBy('created', 'desc')->first();
            
            //print_r($user_prev_exam);
            //print_r($row);
        $data[] = [
            'id'=>$row->id,
            'company_id'=>$row->company_id,
            'campaign_type'=> $row->campaign_type,
            'campaign_code'=> $row->campaign_code,
            'other_parameter' => $row->other_parameter,
            'start_date'=> $row->start_date,
            'slug' => $row->slug,
            'video' => $video,
            'user_id' => (isset($row->user_id)) ? $row->user_id : null,
            'assigned_by' => (isset($row->assigned_by)) ? $row->assigned_by : null,
            'role_name' => (isset($row->role_name)) ? $row->role_name : null,
            'assigned_name' => (isset($row->assigned_name)) ? $row->assigned_name : null,
            'status' => (isset($row->status)) ? $row->status : null,
            'is_active' => $row->is_active,
            'created_at' => (NULL !== $row->created_at)?date('d-M-y', strtotime($row->created_at)):date('d-M-y'),
            'created_by'=>$row->created_by,
            'modified_at'=> $row->modified_at,
            'modified_by'=> $row->modified_by,
            'previous_result'=>$user_prev_exam
            ];
            
        }
                  //exit;
            if($records){
                 $response = array(
                  "draw" => intval($draw),
                  "iTotalRecords" => $totalRecordwithFilter,
                  "iTotalDisplayRecords" => $totalRecords,
                  "aaData" => $data
                );
                return response()->json([
                    'status' => 'success',
                    'data' => $response
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
            try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }  
    }
    
    public function createUserExamResult(Request $request){
        $postData = $request->all();
        
         $validator = Validator::make($request->all(), [
            'campaign_code' => 'required'
        ]);
        // return response()->json(['status'=>'success','data'=>$postData['reference_id']]);
        if($validator->fails()){
             return response()->json(['status'=>'error','message'=>$validator->errors()]);
        }
        
        $check = UserExamResult::
                    where('campaign_code', 'LIKE', $postData['campaign_code'])->
                    where('user_id', $request->user()->id)->
                    count();
                    
        
            
            $data['campaign_code'] = $postData['campaign_code'];
            $data['user_id'] =  $request->user()->id;
            $data['exam_code'] =  $postData['campaign_code'].'-'.$request->user()->id.'-'.($check+1);
            $data['marks'] = '0';
            $data['created'] = date('Y-m-d H:i:s');
            $data['modified'] = date('Y-m-d H:i:s');
            $data['created_by'] = $request->user()->id;
            $data['modified_by'] = $request->user()->id;
            
            $insert = UserExamResult::create($data);
            
            if($insert){
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Examination Created Successfully!',
                    'data' => $insert
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
            try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }  
    }
    
    
    public function submitAnswer(Request $request) {
        //echo "<pre>";print_r($request);exit;
         $postData = $request->all();
        
         $validator = Validator::make($request->all(), [
            'campaign_code' => 'required',
            'exam_code' => 'required',
            'question_id' => 'required',
            'question_ans' => 'required',
            'user_ans' => 'required',
            
        ]);
        // return response()->json(['status'=>'success','data'=>$postData['reference_id']]);
        if($validator->fails()){
             return response()->json(['status'=>'error','message'=>$validator->errors()]);
        }
        $questionAns = $postData['question_ans'];
        $userAns = $postData['user_ans'];
        
        $data['campaign_code'] = $postData['campaign_code'];
        $data['exam_code'] = $postData['exam_code'];
        $data['question_id'] = $postData['question_id'];
        $data['question_ans'] = json_encode($postData['question_ans']);
        $data['user_ans'] = json_encode($postData['user_ans']);
        $data['created'] = date('Y-m-d H:i:s');
        $data['modified'] = date('Y-m-d H:i:s');
        $data['created_by'] = $request->user()->id;
        $data['modified_by'] = $request->user()->id;
        
        
        $correct = 0;
        $wrong = 0;
        $unattended = 0;
        $correctAnswers = 0;
     
        // print_r($postData['user_ans']);exit;
        foreach($questionAns as $key => $value ){
            // print_r($value);
          if($key === 'campaign_answer'){
            foreach($value as $answer){
                  
                  if(in_array($answer['id'],  $userAns) && $answer['is_ans'] === '1'){
                      $correct = $correct + 1;
                  } else if(in_array($answer['id'],  $userAns) && $answer['is_ans'] === '0'){
                       $wrong =  $wrong + 1;
                  } else {
                       $unattended =  $unattended + 1;
                  }
                  
               if($answer['is_ans'] === '1')
               {
                   $correctAnswers = $correctAnswers+1;
               }
            }
          }
            
        }

        
        if($questionAns['all_answer_mandatory'] === '1'){
            if($correctAnswers == $correct){
                $data['marks'] = $questionAns['weightage'];
            } else {
                if( $wrong > 0){
                    $data['marks'] = 0;
                } else if($correct == $correctAnswers){
                    $data['marks'] = $questionAns['weightage'];
                } else {
                    $data['marks'] = $correctAnswers-$unattended;
                }
            }
            
        }
   
        $insert = UserExams::create($data);
        
                   
            if($insert){
            $this->examresultstatus($data['exam_code'], $data['campaign_code'], $data['marks']);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Examination Created Successfully!',
                    'data' => $insert
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    protected function examresultstatus($exam_code,$campaign_code,$marks){
        $examlastinserted = UserExamResult::where('exam_code' , 'LIKE', $exam_code)->orderBy('id', 'desc')->first();
        
        $marks = $marks +$examlastinserted->marks;
        //print_r($marks);exit;
        $getExamCampaignrecord = Campaign::select('other_parameter')->where('campaign_code' , 'LIKE', $campaign_code)->orderBy('id', 'desc')->first();
        $campaign_other_param = json_decode($getExamCampaignrecord->other_parameter);
        //echo "<pre>"; //print_r($campaign_other_param->system_settings->passing_marks);
        if($marks >= $campaign_other_param->system_settings->passing_marks){
            $status = "PASS";
        }
        else{
            $status = "FAIL";
        }//exit;
        $updateExamResult = UserExamResult::where('exam_code',$exam_code)->update(['marks'=>$marks,'status'=>$status]);
        return true;
            
    }
    
    public function uploadExcelCampaign(Request $request){

        try{
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
            $data = $worksheet->toArray();
            $from = [];
            $from['table'] = [];
            $to;
            
     
            $tables = [];
            $columns = [];
            $subColumns = [];
            $subSubColumns= [];
            $mainData = [];
            $count = 0;
            
            foreach($data as $key => $row){
                
                foreach($row as $rKey => $column){
                    if($key>4) {
                        
                        if(!empty($data[1][$rKey])){
                          
                            $table = strtolower(str_replace("*","",str_replace(" ","_", $data[1][$rKey]))); 
                        }
                        if(!empty($data[2][$rKey])){
                            $columns =  strtolower(str_replace("*","",str_replace(" ","_", $data[2][$rKey])));  
                        } 
                        
                        if(!empty($data[3][$rKey])){
                            $subColumns = strtolower(str_replace("*","",str_replace(" ","_", $data[3][$rKey]))); 
                        } else {
                            if($rKey >= 20){
                                $subColumns = '';
                            }
                        }
                        if(!empty($data[4][$rKey])){
                            $subSubColumns = strtolower(str_replace("*","",str_replace(" ","_", $data[4][$rKey]))); 
                        } else {
                            if($rKey >= 20){
                                $subSubColumns = '';
                            }
                        }
                        if(!empty($column)){
                            if(!empty($columns)){
                                if(!empty($subColumns)){
                                    if(!empty($subSubColumns)){
                                   
                                           if($subColumns === 'user_parameters'){
                                               if($subSubColumns === 'type'){
                                                   $mainData[$count][$table][$columns][$subColumns][$column] = $data[$key][$rKey+1];
                                               }
                                           } else if($subColumns === 'roles') {
                                               if($column === 'yes'){
                                                    $mainData[$count][$table][$columns][$subColumns][$subSubColumns] = $subSubColumns;
                                               }
                                           } else {
                                               
                                                $mainData[$count][$table][$columns][$subColumns][$subSubColumns] = $column;
                                           }
                 
                                    } else { 
                                        $mainData[$count][$table][$columns][$subColumns][] = $column;
                          
                                    }
                                } else {
                                    if(($columns == 'answers') || ($columns == 'is_ans')){
                                         $mainData[$count][$table][$columns][] = $column;
                                    } else {
                                        $mainData[$count][$table][$columns] = $column;
                                        
                                    }
                                }
                            } else {
                                  $mainData[$count][$table] = $column;
                            }
                        } 
                    }
                  
                }
                    $count = $count +1;
            }
            $insert = [];
            $input = [];
            $referenceId = ''; 
            $campaign_code = '';
            $inputAnswer = [];
            $response = [];
            foreach($mainData as $key => $table){
                if(isset($table['campaigns'])){
                    
                    if(isset($table['campaigns']['campaign_type'])){
                        $campaign_code = $this->generateCampaignCode($table['campaigns']['campaign_type']);
                    }
                }
                foreach($table as $tkey => $columns){
                    foreach($columns as $ckey => $rows){
                        if($tkey !== 'campaign_answers'){
                            if(is_array($rows)){
                               
                               
                                $input[$ckey] = json_encode($rows);
                            } else {
                                $input[$ckey] = $rows;
                            }
                        } else {
                                
                            if($ckey === 'answers'){
                                foreach($rows as $rkeys => $answers){
                                    if(in_array($answers, $columns['is_ans'])){
                                        $inputAnswer['is_ans'] = 1;
                                    } else {
                                        $inputAnswer['is_ans'] = 0;
                                    }
                                    $inputAnswer[$ckey] = $answers;
                                    $inputAnswer['question_id'] = $input['question_id'];
                                    $insertAnswer = DB::table($tkey)->insert($inputAnswer);
                                }
                            }
                        }
                    } 
                    if($tkey !== 'campaign_answers'){
                        unset($input['question_id']);
                        $input['campaign_code'] = $campaign_code;
                        $input['created_at'] = date('Y-m-d H:i:s');
                        $input['created_by'] = auth()->id();
                        $input['modified_at'] = date('Y-m-d H:i:s');
                        $input['modified_by'] = auth()->id();
                        
                        $insert = DB::table($tkey)->insertGetId($input);
                        
                        if($insert){
                            $input = [];
                            if($tkey === 'campaign_questions'){
                                $input['question_id'] = $insert;
                            }
                            $insert = '';
                        }
                    }
                }
                $response[$campaign_code] = ['success'];
            }
             
            if($response){
                return response()->json(['status'=>'success','message'=>'Files Uploaded', 'data'=>$response]);
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
    
    public function UserExamResultList(Request $request){
        $exam_code = $request->exam_code;
        //$result = DB::table('user_exam_results')->join('user_exams', 'user_exam_results.exam_code', '=', 'user_exams.exam_code')->join('campaigns', 'user_exams.campaign_code', '=', 'campaigns.campaign_code')->where('user_exam_results.exam_code' , 'LIKE', $exam_code)->orderBy('user_exam_results.id', 'desc')->get();
        
        $result = DB::table('user_exam_results')->join('user_exams', 'user_exam_results.exam_code', '=', 'user_exams.exam_code')->where('user_exam_results.exam_code' , 'LIKE', $exam_code)->orderBy('user_exam_results.id', 'desc')->get();//echo "<pre>";print_r($result);exit;
        return $result;
    }
    
    public function getCampaign2(Request $request){
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $user_role = $request->user()->roles()->get();
        DB::enableQueryLog();
        $campaign = Campaign::where('is_active', true);
        $campaign->where('other_parameter', 'LIKE', '%"'.$user_role[0]->slug.'"%');//campaign assessible by check
        
        if($_SERVER['REQUEST_METHOD']==='POST'){
            
            if(NULL!==$request->post('type')){
                $campaign->where('campaign_type', $request->post('type'));
            }
            //print_r($request->post());exit;
        }
        
        //$campaign->get();
        
        //print_r(DB::getQueryLog());
        echo '<pre>';print_r($campaign);exit;

        $query = DB::table('campaigns as c')
            ->where('c.is_active', '1')
            ->where('c.other_parameter', 'LIKE', '%roles%'.$user_role[0]->slug.'%');
                    
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
               $searchQuery = " (t.campaign_code like '%".$searchValue."%' or t.reference_no like '%".$searchValue."%')";
            }
            
            if(isset($postData['assigned']) && (NULL !== $postData['assigned'])){
                $query->join('assign_campaign as ac', 'ac.campaign_code', 'LIKE', 'c.campaign_code', 'left')->
                    join('users as au', 'au.id', '=', 'ac.user_id', 'left')->
                    join('user_roles as aur', 'aur.user_id', '=', 'au.id', 'left')->
                    join('roles as ar', 'ar.id', '=', 'aur.role_id')->
                    select(['c.*', 'ac.user_id', 'ac.created_by as assigned_by', 'ac.status', 'ar.role_name', 'au.name as assigned_name'])->
                        where('ac.user_id', auth()->id());
            }
           
        }
        
        $sql = $query;
        //print_r($sql);exit;
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
        $data = [];
        foreach($records as $keys => $row){
            $video = '';
            $temp = [];
            $temp['other_parameter'] = json_decode( $row->other_parameter, true);
            if(isset($temp['other_parameter']['user_parameters']['video']) && (NULL !==  $temp['other_parameter']['user_parameters']['video'] )){
                // $video =  base64_encode(URL('uploads/videos/'.$temp['other_parameter']['user_parameters']['video']));
                $video =  URL('uploads/videos/'.$temp['other_parameter']['user_parameters']['video']);
            }
        $data[] = [
            'company_id'=>$row->company_id,
            'campaign_type'=> $row->campaign_type,
            'campaign_code'=> $row->campaign_code,
            'other_parameter' => $row->other_parameter,
            'start_date'=> $row->start_date,
            'slug' => $row->slug,
            'video' => $video,
            'user_id' => (isset($row->user_id)) ? $row->user_id : null,
            'assigned_by' => (isset($row->assigned_by)) ? $row->assigned_by : null,
            'role_name' => (isset($row->role_name)) ? $row->role_name : null,
            'assigned_name' => (isset($row->assigned_name)) ? $row->assigned_name : null,
            'status' => (isset($row->status)) ? $row->status : null,
            'is_active' => $row->is_active,
            'created_at' => $row->created_at,
            'created_by'=>$row->created_by,
            'modified_at'=> $row->modified_at,
            'modified_by'=> $row->modified_by
            ];
            
        }
                  
            if($records){
                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
            try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }  
    }
    
    public function ExamResultList(Request $request) {
        //echo "<pre>"; print_r($request);exit;
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $userExamResult;
   
        $query = UserExamResult::select(['users.*', 'user_exam_results.id as exam_result_id', 'exam_code', 'campaign_code', 'marks', 'user_exam_results.status'])
                ->join('users', 'users.id', 'user_id', 'left')
                ->join('states as s', 's.id', '=', 'users.state', 'left')
                ->join('cities as c', 'c.id', '=', 'users.city', 'left');
        $searchQuery = '';
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
               $searchQuery = " (t.campaign_code like '%".$searchValue."%' or t.exam_code like '%".$searchValue."%')";
            }
            
            if(isset($postData['self']) && (NULL !== $postData['self'])){

                       $query->where('user_id', auth()->id());
            }
            
              if(isset($postData['status']) && (NULL !== $postData['status'])){

                      $query->where('user_exam_results.status', 'LIKE', $postData['status']);
            }
            
               if(isset($postData['exam_code']) && (NULL !== $postData['exam_code'])){

                      $query->where('user_exam_results.exam_code', 'LIKE', $postData['exam_code']);
            }
            if(isset($postData['state']) && (NULL !== $postData['state'])){
                
                $query->where('s.id','=',$postData['state']);
                
            }
            if(isset($postData['city']) && (NULL !== $postData['city'])){
                
                $query->where('c.id','=',$postData['city']);
                
            }
            //   if(isset($postData['self']) && (NULL !== $postData['self'])){

            //           $query->where('user_id', auth()->id());
            // }
           
        }
        
         $sql = $query;
        //print_r($sql);exit;
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
        $data = [];
        foreach($records as $keys => $row){
            $data[] = [
                'id'=>$row->exam_result_id,
                'name'=> $row->name,
                'mobile'=> $row->mobile,
                'email' => $row->email,
                'exam_code'=> $row->exam_code,
                'campaign_code' => $row->campaign_code,
                'status' => $row->status,
                'marks' => $row->marks,
                'is_active' => $row->is_active,
                'created_at' => $row->created_at,
                'created_by'=>$row->created_by,
                'modified_at'=> $row->modified_at,
                'modified_by'=> $row->modified_by
            ];
            
        
        }
        if($records){
                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ],200);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
            try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }  
    }
    
    public function downloadCourses(Request $request){
        $pathToFile = URL('/').'/public/upload/excel/Courses.xlsx';
        $headers = [
              'Content-Type' => 'application/pdf',
           ];

        return response()->download($pathToFile, 'Courses.xlsx', $headers);
    }
    
    public function downloadExamsExcel(Request $request){
        $pathToFile = URL('/').'/public/upload/excel/ExamsExcel.xlsx';
        $headers = [
              'Content-Type' => 'application/pdf',
           ];

        return response()->download($pathToFile, 'Courses.xlsx', $headers);
    }

    function generateCampaignCode($type = 'faq') {
        do {
            $code = Helper::generateRandomString(6);
        } while (DB::table('campaigns')->where("campaign_code", "LIKE", $type.'-'.$code)->first());

        return $type.'-'.$code;         
    }
    
    public function uploadDocsCampaign(Request $request){
    
        try{
            $input = $request->all();   
            $msg = '';
            $up_record = [];
            $getExamCampaignrecord = Campaign::select('other_parameter')->where('campaign_code' , 'LIKE', $input['campaign_code'])->orderBy('id', 'desc')->first();
            $campaign_other_param = json_decode($getExamCampaignrecord->other_parameter);
            $up_record = json_decode($campaign_other_param->user_parameters);
            // echo "<pre>"; print_r(json_decode($up_record));exit;
            
            if($request->hasFile('upload_file')){
                    //echo "<pre>"; print_r($request->all());exit;
                    $files = $request->file('upload_file');
                    $temp = [];
                    foreach($files as $type => $other_images){
                        //echo "<pre>";print_r($other_images);exit;
                    
                            $original_filename = $other_images->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $other_images->getMimeType();
                            
                            $type = explode('/', $file_type);
                            $type = $type[0];
                            $image = $original_filename_arr[0].time(). '.'.$file_ext;
                            if ($other_images->move('./public/upload/'.$request->post('category').'/'.$type.'/', $image)) {
                                    
                                    // if(!isset($temp[$type])){
                                        
                                    //     $temp[$type][] = $up_record->$type;
                                    // }
                                    $upPostData['file'] = $original_filename;
                                    $upPostData['description'] = $original_filename_arr[0];
                                    
                                    /*$postData['category'] = $request->post('category');
                                    $postData['name'] = $image;
                                    $postData['type'] = $request->post('type');*/
                                    // $temp[] =
                                    $temp[$type][] = array_push($up_record[$type], $upPostData);
                                    
                                    
                                    
                                    
                                    // echo "<pre>"; print_r($up_record);exit;
                                    // $up_record->image[$type][] = json_decode($up_record->image[$type]);
                                    // $up_record->image[$type][] = $upPostData;
                                    $msg.='Images has been uploaded'; 
                                    //echo "<pre>";print_r($upPostData);exit;
                                } 
                            // if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                            //     //echo $file_type;
                            //     $type = 'image';
                            // 	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                            
                            // } 
                            //     if($file_ext == 'mp4' || $file_ext == '3gp'){
                            //          $type = 'video';
                            //     //echo $file_ext;//echo $request->post('type');exit;
                            // 	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                            
                            //     if ($other_images->move('./public/upload/'.$request->post('category').'/'.$request->post('type').'/', $image)) {
                            
                                    
                            //     } 
                            // }
                            
                            // if($file_ext == 'pdf'){
                            //     echo $file_ext;
                            // 	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                            
                            //     $type = 'pdf';
                            //     if ($other_images->move('./public/upload/'.$request->post('category').'/'.$request->post('type').'/', $image)) {
                            //         $upPostData['file'] = $original_filename;
                            //         $upPostData['description'] = $original_filename_arr[0];
                                    
                            //         /*$postData['name'] = $image;
                            //         $postData['category'] = $request->post('category');
                            //         $postData['type'] = $request->post('type');*/
                            //         $up_record->pdf[] = $upPostData;
                            //         $msg.='Pdf has been uploaded'; 
                            //     } 
                            // }
                            
                            /*$postData['create'] = date('Y-m-d H:i:s');
                            $postData['created_by'] = $request->user()->id;*/
                        
                         
                       //$post['other_images'][] = UploadData::create($postData);
                        // $post['product_images'][$key] = $temp;
                        
                    }
                    $up_record = json_encode($temp);
                    echo "<pre>";print_r($up_record);exit;
                   $msg = 'File has been Uploaded';
                }
                 //if($request->has('upload_file')){
                //     //echo 'upload link';
                //     $files = $request->post('upload_file');
                    
                //     foreach($files as $type => $other_images){
                //         //print_r($other_images);
                //         //print_r($type);
                //         if($type == 'link'){
                            
                //             echo $type;
                //             $upPostData['file'] = $other_images;
                //             $upPostData['description'] = $type;
                                    
                //             /*$postData['name'] = $other_images;
                //             $postData['category'] = $request->post('category');
                //             $postData['type'] = $type;
                //             $postData['create'] = date('Y-m-d H:i:s');
                //             $postData['created_by'] = $request->user()->id;*/
                //             //print_r($postData);
                //             $up_record->link[] = $upPostData;
                //             //$post['other_images'][] = UploadData::create($postData);
                //             $msg.='Link has been uploaded'; 
                //             echo "<pre>";print_r($up_record);
                //         }
                //     }//exit;
                // }
                exit;
                $campaign_other_param->user_parameters = $up_record;
                // $getExamCampaignrecord->other_parameter = ;
            //echo "<pre>";print_r($up_record);exit;
            $updateExamResult = campaign::where('campaign_code',$input['campaign_code'])->update(['other_parameter'=>json_encode($campaign_other_param)]);
            
            //return true;
            //echo "<pre>";print_r($input['upload_file']);exit;
            // foreach($input['upload_file'] as $upkeys=>$upvalues){
            //     //echo "<pre>";print_r($upvalues);
            //     if($upkeys = 'link'){
            //         //echo 'link';
            //     }else{
            //         //echo 'file';
            //         if($request->hasFile($upvalues)){echo 'file';}
            //     }
            // }exit;
            
               /* $chk_ext = explode('.',$fname);
                $filename = $_FILES['excel_file']['tmp_name'];*/
                /*if(end($chk_ext)=='xlsx') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                }*/
                /*if(end($chk_ext)=='xls') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }*/
                if($campaign_other_param){
                     return response()->json([
                        'status' => "success",
                        'message' => $msg,
                        'data' => $campaign_other_param
                    ],200);
                } else {
                     return response()->json([
                        'status' => "success",
                        'message' => $msg,
                        'data' => $campaign_other_param
                    ],200);
                }
            /* $response = $post;
            
             
            if($response){
                return response()->json(['status'=>'success','message'=>'Files Uploaded', 'data'=>$post]);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }*/
       
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function UserExamList(Request $request){
        try{
            if(NULL === $request->post('exam_code')){
                 return response()->json(['status'=>'error','message'=>'Invalid Request']);
            }
            
            $exam_code = $request->post('exam_code');
            //$result = DB::table('user_exam_results')->join('user_exams', 'user_exam_results.exam_code', '=', 'user_exams.exam_code')->join('campaigns', 'user_exams.campaign_code', '=', 'campaigns.campaign_code')->where('user_exam_results.exam_code' , 'LIKE', $exam_code)->orderBy('user_exam_results.id', 'desc')->get();
            
            $result = DB::table('user_exams')->where('exam_code' , 'LIKE', $exam_code)->orderBy('id', 'desc')->get();//echo "<pre>";print_r($result);exit;
            
            if($result){
                
                foreach($result as $keys => $row){
                    $result[$keys]->question_ans = json_decode($row->question_ans,true);
                    //  $result[$keys]->question_ans['campaign_answer'] = $row->question_ans['campaign_answer'];
                     $result[$keys]->user_ans = json_decode($result[$keys]->user_ans);
                    // // return $can_ans;
                    // $que_camp_ans = json_decode($que_ans['campaign_answer'],true);
                    // return $result;
                   
                }
                return response()->json(['status'=>'success','message'=>'User Result', 'data'=> $result]);
            } else {
                return response()->json(['status'=>'error','message'=>'No Record Found']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        return $result;
    }
    
    public function UserExamCampaignList(Request $request){
        try{
            if(NULL === $request->post('exam_code')){
                 return response()->json(['status'=>'error','message'=>'Invalid Request']);
            }
            $postData = $request->post();
            
            $query = UserExamResult::select('*');
            if(isset($postData['exam_code']) && (NULL !== $postData['exam_code'])){
                
                $query->where('exam_code', 'LIKE',$postData['exam_code']);
                
            }
            if(isset($postData['user_id']) && (NULL !== $postData['user_id'])){
                
                $query->where('user_exam_results.user_id','=',$postData['user_id']);
                
            }
            $result = $query->orderBy('user_exam_results.id', 'desc')->get();
            //echo "<pre>";print_r($result);exit;
           
            if($result){
                
                foreach($result as $keys => $row){
                    $campaign_detail = Campaign::where('campaign_code',$row->campaign_code)->first();
                    $campaign_detail->other_parameter = json_decode($campaign_detail->other_parameter,true);
                    $result[$keys]->campaign = $campaign_detail;
                }
                return response()->json(['status'=>'success','message'=>'User Exam Result Detail', 'data'=> $result]);
            } else {
                return response()->json(['status'=>'error','message'=>'No Record Found']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        return $result;
    }
    
    public function uploadExcelCampaignUpdate(Request $request){

        try{
            //print_r($_FILES);exit;
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
            $data = $worksheet->toArray();
            $from = [];
            $from['table'] = [];
            $to;
            
     
            $tables = [];
            $columns = [];
            $subColumns = [];
            $subSubColumns= [];
            $mainData = [];
            $count = 0;
            
            foreach($data as $key => $row){
                
                foreach($row as $rKey => $column){
                    if($key>4) {
                        
                        if(!empty($data[1][$rKey])){
                          
                            $table = strtolower(str_replace("*","",str_replace(" ","_", $data[1][$rKey]))); 
                        }
                        if(!empty($data[2][$rKey])){
                            $columns =  strtolower(str_replace("*","",str_replace(" ","_", $data[2][$rKey])));  
                        } 
                        
                        if(!empty($data[3][$rKey])){
                            $subColumns = strtolower(str_replace("*","",str_replace(" ","_", $data[3][$rKey]))); 
                        } else {
                            if($rKey >= 20){
                                $subColumns = '';
                            }
                        }
                        if(!empty($data[4][$rKey])){
                            $subSubColumns = strtolower(str_replace("*","",str_replace(" ","_", $data[4][$rKey]))); 
                        } else {
                            if($rKey >= 20){
                                $subSubColumns = '';
                            }
                        }
                        if(!empty($column)){
                            if(!empty($columns)){
                                if(!empty($subColumns)){
                                    if(!empty($subSubColumns)){
                                   
                                           if($subColumns === 'user_parameters'){
                                               if($subSubColumns === 'type'){
                                                   $mainData[$count][$table][$columns][$subColumns][$column] = $data[$key][$rKey+1];
                                               }
                                           } else if($subColumns === 'roles') {
                                               if($column === 'yes'){
                                                    $mainData[$count][$table][$columns][$subColumns][$subSubColumns] = $subSubColumns;
                                               }
                                           } else {
                                               
                                                $mainData[$count][$table][$columns][$subColumns][$subSubColumns] = $column;
                                           }
                 
                                    } else { 
                                        $mainData[$count][$table][$columns][$subColumns][] = $column;
                          
                                    }
                                } else {
                                    if(($columns == 'answers') || ($columns == 'is_ans')){
                                         $mainData[$count][$table][$columns][] = $column;
                                    } else {
                                        $mainData[$count][$table][$columns] = $column;
                                        
                                    }
                                }
                            } else {
                                  $mainData[$count][$table] = $column;
                            }
                        } 
                    }
                  
                }
                    $count = $count +1;
            }
            $insert = [];
            $input = [];
            $referenceId = ''; 
            $campaign_code = '';
            $inputAnswer = [];
            $response = [];
            foreach($mainData as $key => $table){
                if(isset($table['campaigns'])){
                    
                    if(isset($table['campaigns']['campaign_type'])){
                        $campaign_code = $this->generateCampaignCode($table['campaigns']['campaign_type']);
                    }
                }
                foreach($table as $tkey => $columns){
                    foreach($columns as $ckey => $rows){
                        if($tkey !== 'campaign_answers'){
                            if(is_array($rows)){
                               
                               
                                $input[$ckey] = json_encode($rows);
                            } else {
                                $input[$ckey] = $rows;
                            }
                        } else {
                                
                            if($ckey === 'answers'){
                                foreach($rows as $rkeys => $answers){
                                    if(in_array($answers, $columns['is_ans'])){
                                        $inputAnswer['is_ans'] = 1;
                                    } else {
                                        $inputAnswer['is_ans'] = 0;
                                    }
                                    $inputAnswer[$ckey] = $answers;
                                    $inputAnswer['question_id'] = $input['question_id'];
                                    $insertAnswer = DB::table($tkey)->insert($inputAnswer);
                                }
                            }
                        }
                    } 
                    if($tkey !== 'campaign_answers'){
                        unset($input['question_id']);
                        $input['campaign_code'] = $campaign_code;
                        $input['created_at'] = date('Y-m-d H:i:s');
                        $input['created_by'] = auth()->id();
                        $input['modified_at'] = date('Y-m-d H:i:s');
                        $input['modified_by'] = auth()->id();
                        
                        $insert = DB::table($tkey)->insertGetId($input);
                        
                        if($insert){
                            $input = [];
                            if($tkey === 'campaign_questions'){
                                $input['question_id'] = $insert;
                            }
                            $insert = '';
                        }
                    } 
                     
                }
        
             $response[$campaign_code] = ['success'];
            }
             
            if($response){
                return response()->json(['status'=>'success','message'=>'Files Uploaded', 'data'=>$response]);
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
    
    public function dashboardDetailsList(){
        echo "<pre>dashboardDetailsList";print_r();exit;
    }
    
}