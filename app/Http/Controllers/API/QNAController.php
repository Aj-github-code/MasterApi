<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Config;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use App\Helpers\Helper as Helper;
use App\Models\QNAMgmtCampaign;
use App\Models\QNAMgmtCampaignQuestion;
use App\Models\QNAMgmtCampaignAnswer;
use App\Http\Controllers\API\QNAController;
use App\Models\QNAMgmtAssignCampaign;

class QNAController extends Controller
{
    protected $type;
    public function __construct(){
       $this->type = 'faq';
    }
    
    public function index() {
        
    }
    
    public function getQuestion(Request $request)
    {
        $companyTablePrefix = session('company_table_name');
        DB::enableQueryLog();
        
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
             return response()->json(['status'=>'error','message'=>$validator->errors()]);
   
        }
        
        $input = $request->all();

        $question = QNAMgmtCampaignQuestion::where('campaign_questions.id', $input['id'])->join($companyTablePrefix.'campaigns as c', 'c.campaign_code', '=', 'campaign_questions.campaign_code', 'left')->join($companyTablePrefix.'campaign_answers as ca', 'ca.question_id', '=', 'campaign_questions.id')->select('campaign_questions.*','ca.answers','ca.question_id','ca.is_ans','c.role_id', 'c.user_id', 'c.company_id','c.slug')->get();
        // print_r($input);exit;
        $msg = 'Some Error Occurred';
        if($question){
            $msg = 'Questions fetched successfully!';
            return response()->json(['status'=>'success','message'=>$msg]);
        } else {
            return response()->json(['status'=>'error','message'=>'Data Invalid Access']);
        }
    }
    
    public function createCampaign(Request $request) {
        $params = $request->all();
        if(isset($params['type']) && !empty($params['type'])) {
            $this->type = $params['type'];
        }
        $requiredInputs = [];
        $requiredInputs = config('qna.campaign.'.$this->type.'.validate');
        
        $postData['type'] = $this->type;
        if($requiredInputs){
            $validator = Validator::make($request->all(), $requiredInputs);
            if ($validator->fails()) {
                return response()->json(['status'=>'error','message'=> $validator->errors()]);
            }
            
            foreach($requiredInputs as $ikey => $input) {
                $postData[$ikey] = $params[$ikey];
            }
        } 
        
        $postData['user_id'] = auth()->id();
        // $postData['company_id'] = auth()->company_id();
        $postData['campaign_code'] = $this->generateCampaignCode($this->type);
        
        if(isset($params['video']) && (NULL !== $params['video'])) {
            $video = $params['video'];
            $input = time().'.'.$video->getClientOriginalExtension();
            $destinationPath = 'uploads/videos';
            $video->move($destinationPath, $input);
    
            $params['video']       = $input;
            
        } else {
            $params['video']       ='';
        }
        
        
        $otherParameter['timer'] = isset($params['timer'])?$params['timer']:'';
        $otherParameter['reference_id'] = isset($params['reference_id'])?$params['reference_id']:'';
        $otherParameter['title'] = isset($params['title'])?$params['title']:'';
        $otherParameter['comment'] = isset($params['comment'])?$params['comment']:'';
        $otherParameter['video'] = isset($params['video'])?$params['video']:'';
        
        $postData['other_parameter'] = json_encode($otherParameter);
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['created_by'] = auth()->id();
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = auth()->id();
        
            $insert = QNAMgmtCampaign::insert($postData);
            
            if($insert){
                return response()->json(['status'=>'success', 'data'=>$insert, 'message'=> 'Campaign Created Successfully']);
        
    	        
    	    }else{
    	            return response()->json(['status'=>'error', 'message'=> 'Campaign Not Created']);
    	      
    	    }
        try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error', 'message'=> $error[1]]);
      
        } catch(Exception $ex) {
            return response()->json(['status'=>'error', 'message'=> $ex->getMessage()]);
          
        }
    }
    
    public function createCampaignNew(Request $request) {
        $companyTablePrefix = session('company_table_name');
        $params = $request->all();
        if(isset($params['type']) && !empty($params['type'])) {
            $this->type = $params['type'];
        }
        $requiredInputs = [];
        $requiredInputs = config('qna.campaign.'.$this->type.'.validate');
        
        $postData['type'] = $this->type;
        
        //print_r($requiredInputs);exit;
        if(count($requiredInputs)>0){
            $validator = Validator::make($request->all(), $requiredInputs);
            if ($validator->fails()) {
                // return $this->sendError('Validation Error.', $validator->errors());
                 return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
            }
            
            foreach($requiredInputs as $ikey => $input) {
                // echo "hi";exit;
                $postData[$ikey] = $params[$ikey];
            }
        } 
        
        $postData['user_id'] = auth()->id();
        // $postData['company_id'] = auth()->company_id();
        $postData['campaign_code'] = $this->generateCampaignCode($this->type);
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['created_by'] = auth()->id();
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = auth()->id();
        // print_r($postData);exit;
        try{
            $insert = QNAMgmtCampaign::insert($postData);
            
            if($insert){
                 return response()->json(['status'=>'success', 'message'=>'Campaign Created Successfully','data'=>$postData]);
                // return json_encode(['status'=>'success', 'data'=>$postData, 'message'=>'Campaign Created Successfully']);
                //  return $this->sendResponse($postData, 'Campaign Created Successfully');
    	        
    	    }else{
    	         return json_encode(['status'=>'error', 'message'=>'Campaign Not Created']);
    	    }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
             return json_encode(['status'=>'error', 'message'=>$error[1]]);
    	    
        
        } catch(Exception $ex) {
             return json_encode(['status'=>'error', 'message'=>$ex->getMessage()]);
        }
    }
    
    public function createCampaignQuestion(Request $request) {
        
        $params = $request->all();
        if(isset($params['type']) && !empty($params['type'])) {
            $this->type = $params['type'];
        }
        $requiredInputs = [];
        $requiredInputs = config('qna.questions.'.$this->type.'.validate');
        if(count($requiredInputs)>0){
            
            $validator = Validator::make($request->all(), $requiredInputs);
            if ($validator->fails()) {
                return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
            }
        }
        try{
          
        // echo "hiihu";exit;
            $request->type = $this->type;

            $campaign = QNAMgmtCampaign::where('type', 'LIKE', $this->type)->where('role_id', '=', $request->role_id)->orderBy('id', 'DESC')->first();
            //  $campaign = DB::table('campaigns')->where('campaign_code', 'LIKE', $postData['campaign_code'])->orderBy('id', 'DESC')->first();
			
            if($campaign) {
                $postData['campaign_code'] = $campaign->campaign_code;
            } else {
                $createCampaign = json_decode($this->createCampaignNew($request),true);
                
                if(isset($createCampaign['success']) && (NULL !== $createCampaign['success'])) {
                      $postData['campaign_code'] = $createCampaign['data']['campaign_code'];
                } else {
                    return response()->json(['status'=>'error', 'message'=> 'Campaign Not Created']);
               
                }
            }
            

            foreach($params['campaign'] as $key => $column) {
                $insertData = [];
                $optionArray = [];
                foreach($column as $ckey => $value) {
                    if(!empty($value)){
                        if($ckey == "options") {
                          $optionArray = $value;
                        } else {
                            $insertData[$ckey] = $value;
                        }
                    }
                }
                $insertData['campaign_code'] = $postData['campaign_code'];
                $insertData['created_at'] = date('Y-m-d H:i:s');
                $insertData['created_by'] = auth()->id();
                $insertData['modified_at'] = date('Y-m-d H:i:s');
                $insertData['modified_by'] = auth()->id();
                $findQuestion = '';
                if(isset($insertData['question_code']) && !empty($insertData['question_code'])){
                    
                } else {
                    $insertData['question_code'] = $this->generateQuestionCode($this->type);
                }
				
                $insert = QNAMgmtCampaignQuestion::updateOrCreate(['question_code'=>$insertData['question_code']],$insertData);
                if($insert){
                    if(count($optionArray)>0){
                        foreach($optionArray as $akey => $options){
                            $insertOption = [];
                            foreach($options as $okey => $option) {
                                $insertOption['question_id'] = $insert->id;
                                $insertOption[$okey] = $option;
                            }
                            
                            $insertOption['created_at'] = date('Y-m-d H:i:s');
                            $insertOption['created_by'] = auth()->id();
                            $insertOption['modified_at'] = date('Y-m-d H:i:s');
                            $insertOption['modified_by'] = auth()->id();

                	        $insertAnswer = QNAMgmtCampaignAnswer::updateOrCreate(['question_id'=>$insertOption['question_id'], 'answers'=>$insertOption['answers']],$insertOption);
                        }
                    }
        	    }
            }
            
            if($insert){
                return response()->json(['status'=>'success', 'data'=>[], 'message'=> 'Campaign Questions Created Successfully']);
    	    }else{
    	         return response()->json(['status'=>'error', 'message'=> 'Campaign Not Created']);


    	    }
    	    
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
             return response()->json(['status'=>'error', 'message'=> $error[1]]);
         
        } catch(Exception $ex) {
             return response()->json(['status'=>'error', 'message'=> $ex->getMessage()]);
        
        }
        
    }
    
    
    public function createCampaignAnswers(Request $request) {
        $params = $request->all();
        
        $requiredInputs = [];
        $requiredInputs = config('qna.answers.validate');
        
        if(count($requiredInputs)>0){
            $validator = Validator::make($request->all(), $requiredInputs);
            if ($validator->fails()) {
                return response()->json(['status'=>'error', 'message'=> $validator->errors()]);       
            }
        }
        
        try{
            
            $insertData['question_id'] = $params['question_id'];
            $insertData['is_ans'] = $params['is_ans'];
            $insertData['answers'] = $params['answers'];
            if(isset($params['is_active']) && !empty($params['is_active'])) {
                $insertData['is_active'] = $params['is_active'];
            }
            $insertData['created_at'] = date('Y-m-d H:i:s');
            $insertData['created_by'] = auth()->id();
            $insertData['modified_at'] = date('Y-m-d H:i:s');
            $insertData['modified_by'] = auth()->id();
                
            if(isset($params['id']) && !empty($params['id'])){
                 $update = QNAMgmtCampaignAnswer::where('id', '=', $params['id'])->update($insertData);
                 if($update){
                     return response()->json(['status'=>'success', 'message'=> 'Campaign Answer Updated Successfully']); 
        	    }else{
	               return response()->json(['status'=>'error', 'message'=> 'Campaign Answer Not Updated']);
        	    }
            }else{
                $insert = QNAMgmtCampaignAnswer::insert($insertData);
                if($insert){
                    return response()->json(['status'=>'success', 'message'=> 'Campaign Answer Created Successfully']);
        	    }else{
        	        return response()->json(['status'=>'error', 'message'=> 'Campaign Answer Not Created']);
        	    }
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
                   return response()->json(['status'=>'error', 'message'=> $error[1]]);
           
        } catch(Exception $ex) {
                   return response()->json(['status'=>'error', 'message'=> $ex->getMessage()]);
           
        }
        
        
    }
    
    public function questionList(Request $request) {
        $params = $request->all();
        
        // if(isset($params['type']) && !empty($params['type'])) {
        //     $this->type = $params['type'];
        // }
        
        // $requiredInputs = [];
        // $requiredInputs = config('qna.listing.'.$this->type.'.validate');
        // if(count($requiredInputs)>0){
            
        //     $validator = Validator::make($request->all(), $requiredInputs);
        //     if ($validator->fails()) {
        //          return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
        //     }
        // }

        $list = QNAMgmtCampaignQuestion::with('campaign_answer')
                ->where('campaign_code', 'LIKE', $params['campaign_code'])
                ->get();
                    
        if($list){
            return response()->json(['status'=>'success', 'data'=>$list, 'message'=> 'Campaign Questions And Answers List']);
	    }else{
	        return response()->json(['status'=>'error', 'message'=> 'Campaign Questions Not Found']);

	    }
    }
    
    
      public function qnaAjaxList(Request $request){
          $companyTablePrefix = session('company_table_name');
          	$postData = $request->all();
			//print_r($this->session->userdata('token'));exit;
// 		 $postData = $request->post();
//         //echo '<pre>';print_r($postData);exit;
//         ## Read value
//         $draw = isset($postData['draw'])?$postData['draw']:'';
//         $start = isset($postData['start'])?$postData['start']:0;
//         $rowperpage = isset($postData['length'])?$postData['length']:10; // Rows display per page
//         $columnIndex = isset($postData['order'])?$postData['order'][0]['column']:''; // Column index
//         $columnName = isset($postData['columns'])?$postData['columns'][$columnIndex]['data']:'id'; // Column name
//         $columnSortOrder = isset($postData['order'])?$postData['order'][0]['dir']:'DESC'; // asc or desc
//         $searchValue = isset($postData['search']['value'])?$postData['search']['value']:''; // Search value
        
           $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        
         $searchQuery = '';
               $searchQuery = '';
        // print_r($stalls->toSql());exit;
        // $searchQuery = ' 1 = 1';
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
              $searchQuery = " AND (t.question_code like '%".$searchValue."%' or t.question like '%".$searchValue."%' or t.type like '%".$searchValue."%' or t.role_name like '%".$searchValue."%')";
            }
        }
        
       
		

        $sql = 'SELECT c.type, cq.question_code,cq.campaign_code, cq.is_active, cq.question, cq.id, r.role_name';
        // $sql .= ' (SELECT answers FROM campaign_answers WHERE question_id = cq.id ) as answers';
        $sql .= ' FROM  '.$companyTablePrefix.'campaign_questions as cq';
        $sql .= ' LEFT JOIN '.$companyTablePrefix.'campaigns as c  ON cq.campaign_code LIKE c.campaign_code';
        $sql .= ' LEFT JOIN roles as r ON r.id = c.role_id';
        $sql .= ' WHERE 1=1';
        if(isset($postData['type'])){
            if(NULL !== $postData['type'] && !empty($postData['type'])){
                
                $sql .= ' AND c.type LIKE "'.$postData['type'].'"';
               
            }
        }
        if(isset($postData['campaign_code'])){
            if(NULL !== $postData['campaign_code'] && !empty($postData['campaign_code'])){
                $sql->where('cq.campaign_code', 'LIKE', $postData['campaign_code']);
                 $sql .= ' AND cq.campaign_code LIKE "'.$postData['campaign_code'].'"';
            }
        }
          if(isset($postData['role_id'])){
            if(NULL !== $postData['role_id'] && !empty($postData['role_id'])){
                $sql .= ' AND c.role_id = "'.$postData['role_id'].'"';
            }
        }
         if(isset($postData['is_active'])){
            if(NULL !== $postData['is_active'] ){
                $sql .= ' AND cq.is_active = "'.$postData['is_active'].'"';
            }
        }
		
	    $sql2 = 'Select count(*) as allcount from ('.$sql.') r';
        //echo $sql2;exit;
        $records = DB::select($sql2);
        $totalRecords = $records[0]->allcount;
  
   		
   		$sql2 = 'Select count(*) as allcount from ('.$sql.') t where 1=1 '.$searchQuery;
        $records = DB::select($sql2);
        $totalRecordwithFilter = $records[0]->allcount;
   		
        $sql2 = 'Select * from ('.$sql.') t where 1=1 '.$searchQuery.' order by '.$columnName.' '.$columnSortOrder;
        if ($rowperpage!='-1') {
            $sql2.=' LIMIT '.$start.', '.$rowperpage;
        }
        //echo $sql2;exit;
        $records = DB::select($sql2, [$request->user()->id]);
        /*print_r(DB::getQueryLog());exit;
        echo '<pre>';print_r($records);exit;*/
        $data = array();
        foreach($records as $recordKey => $record ){
            $answers = [];
            $answer = QNAMgmtCampaignAnswer::select('answers')->where('question_id' , '=', $record->id)->get();
            // if($answer){
            //     foreach($answer as $avalue) {
            //         $answers = $avalue->answers;
            //     }
            // }

            $data[$recordKey] = array(
                "sr_no" => $recordKey+1,
                "id"=>$record->id,
                "type"=>$record->type,
                "question_code"=>$record->question_code,
                "campaign_code"=>$record->campaign_code,
                "question"=>$record->question,
                "question_id"=>$record->id,
                "role"=>!empty($record->role_name)?$record->role_name:'All',
                "answers"=>$answer,
                "is_active"=>$record->is_active,
                'action'=>'Action'
            );

        }
        //echo "<pre>"; print_r($data);exit;
        ## Response
        $response = array(
           "draw" => intval($draw),
           "iTotalRecords" => $totalRecordwithFilter,
           "iTotalDisplayRecords" => $totalRecords,
           "aaData" => [
                            ["sr_no"=> 1,
                                    "id"=> "1",
                                    "type"=> "question",
                                    "question_code"=> "faq-lmv79r",
                                    "campaign_code"=> "faq-1V5w0m",
                                    "question"=> "Grill",
                                    "question_id"=> "2",
                                    "role"=> "Dealer",
                                    "answers"=> [
                                        [
                                            "answers"=> "safe"
                                        ],
                                        [
                                            "answers"=> "scratch"
                                        ],
                                        [
                                            "answers"=> "pressed"
                                        ],
                                        [
                                            "answers"=> "broken"
                                        ]
                                    ],
                                    "is_active"=> "0",
                                    "action"=> "Action"
                                ],
                            [
                                "sr_no"=> 2,
                                "id"=> "2",
                                "type"=> "question",
                                "question_code"=> "faq-lmv79r",
                                "campaign_code"=> "faq-1V5w0m",
                                "question"=> "Bonnet",
                                "question_id"=> "2",
                                "role"=> "Dealer",
                                "answers"=> [
        [
            "answers"=> "safe"
        ],
        [
            "answers"=> "scratch"
        ],
        [
            "answers"=> "pressed"
        ],
        [
            "answers"=> "broken"
        ]
    ],
    "is_active"=> "0",
    "action"=> "Action"
],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Left Front Door",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Right Rear Door",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Left Pillar A",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Seat",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Head Light",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Rear Mirror",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Front Mirror",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Right Pillar A",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Whom",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "HeadLight",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                            ["sr_no"=> 1,
                            "id"=> "2",
                            "type"=> "question",
                            "question_code"=> "faq-lmv79r",
                            "campaign_code"=> "faq-1V5w0m",
                            "question"=> "Whom",
                            "question_id"=> "2",
                            "role"=> "Dealer",
                            "answers"=> [
                                [
                                    "answers"=> "safe"
                                ],
                                [
                                    "answers"=> "scratch"
                                ],
                                [
                                    "answers"=> "pressed"
                                ],
                                [
                                    "answers"=> "broken"
                                ]
                            ],
                            "is_active"=> "0",
                            "action"=> "Action"
                            ],
                        ]//$data
            );
          return response()->json($response);
	}
	

	public function CampaignList(Request $request){
        $companyTablePrefix = session('company_table_name');
			//print_r($this->session->userdata('token'));exit;
		 $postData = $request->post();
        //echo '<pre>';print_r($postData);exit;
        ## Read value
        $draw = isset($postData['draw'])?$postData['draw']:'';
        $start = isset($postData['start'])?$postData['start']:0;
        $rowperpage = isset($postData['length'])?$postData['length']:10; // Rows display per page
        $columnIndex = isset($postData['order'])?$postData['order'][0]['column']:''; // Column index
        $columnName = isset($postData['columns'])?$postData['columns'][$columnIndex]['data']:'id'; // Column name
        $columnSortOrder = isset($postData['order'])?$postData['order'][0]['dir']:'DESC'; // asc or desc
        $searchValue = isset($postData['search']['value'])?$postData['search']['value']:''; // Search value
        
         $searchQuery = '';
        
        if($searchValue != ''){
              $searchQuery = " AND (t.question_code like '%".$searchValue."%' or t.question like '%".$searchValue."%' or t.type like '%".$searchValue."%' or t.role_name like '%".$searchValue."%')";
        }
			$postData = $request->all();

        $sql = 'SELECT c.*, r.role_name';
        // $sql .= ' (SELECT answers FROM campaign_answers WHERE question_id = cq.id ) as answers';
        $sql .= ' FROM  '.$companyTablePrefix.'campaigns as c';
        // $sql .= ' LEFT JOIN campaigns as c  ON cq.campaign_code LIKE c.campaign_code';
        $sql .= ' LEFT JOIN roles as r ON r.id = c.role_id';
        $sql .= ' WHERE 1=1';
        if(isset($postData['type'])){
            if(NULL !== $postData['type'] && !empty($postData['type'])){
                
                $sql .= ' AND c.type LIKE "'.$postData['type'].'"';
               
            }
        }
        if(isset($postData['campaign_code'])){
            if(NULL !== $postData['campaign_code'] && !empty($postData['campaign_code'])){
                $sql->where('cq.campaign_code', 'LIKE', $postData['campaign_code']);
                 $sql .= ' AND cq.campaign_code LIKE "'.$postData['campaign_code'].'"';
            }
        }
          if(isset($postData['role_id'])){
            if(NULL !== $postData['role_id'] && !empty($postData['role_id'])){
                $sql .= ' AND c.role_id = "'.$postData['role_id'].'"';
            }
        }
         if(isset($postData['is_active'])){
            if(NULL !== $postData['is_active'] ){
                $sql .= ' AND cq.is_active = "'.$postData['is_active'].'"';
            }
        }
		
	    $sql2 = 'Select count(*) as allcount from ('.$sql.') r';
        //echo $sql2;exit;
        $records = DB::select($sql2);
        $totalRecords = $records[0]->allcount;
  
   		
   		$sql2 = 'Select count(*) as allcount from ('.$sql.') t where 1=1 '.$searchQuery;
        $records = DB::select($sql2);
        $totalRecordwithFilter = $records[0]->allcount;
   		
        $sql2 = 'Select * from ('.$sql.') t where 1=1 '.$searchQuery.' order by '.$columnName.' '.$columnSortOrder;
        if ($rowperpage!='-1') {
            $sql2.=' LIMIT '.$start.', '.$rowperpage;
        }
        //echo $sql2;exit;
        $records = DB::select($sql2, [$request->user()->id]);
        /*print_r(DB::getQueryLog());exit;
        echo '<pre>';print_r($records);exit;*/
        $data = array();
        foreach($records as $recordKey => $record ){
        

            $data[$recordKey] = array(
                "sr_no" => $recordKey+1,
                "id"=>$record->id,
                "type"=>$record->type,
                
                "campaign_code"=>$record->campaign_code,
          
                // "role"=>!empty($record->role_name)?$record->role_name:'All',
                
                "is_active"=>$record->is_active,
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
        
        return json_encode(['status'=>'success', 'data'=>$response, 'message'=>'Campaign List']);
        // return $this->sendResponse($response, 'Campaign List');
	}
	
	
	public function deleteQuestion(Request $request)
    {
        DB::enableQueryLog();
        
        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
            'id' => 'required'
        ]);
   
        if($validator->fails()){
	        return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
        }
        
        $input = $request->all();
        
        $postData['is_active'] = (!$input['is_active'])?0:1;
        
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = $request->user()->id;
        $question = QNAMgmtCampaignQuestion::where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($question){
            $question = QNAMgmtCampaignQuestion::where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        return response()->json(['status'=>'error', 'data'=>$question, 'message'=> $msg]);
   
    }
	
    function generateCampaignCode($type = 'faq') {
        do {
            $code = Helper::generateRandomString(6);
        } while (DB::table('campaigns')->where("campaign_code", "LIKE", $type.'-'.$code)->first());

        return $type.'-'.$code;
    }
    
     function generateQuestionCode($type = 'faq') {
         do {
            $code = Helper::generateRandomString(6);
        } while (DB::table('campaign_questions')->where("question_code", "LIKE", $type.'-'.$code)->first());

        return $type.'-'.$code;
    }


    public function AssignCampaign(Request $request){
        //echo "<pre>"; print_r($request);exit;
          $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'campaign_code' => 'required'
        ]);
        
        if($validator->fails()){
	        return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
        }
        
        $chk = QNAMgmtAssignCampaign::where('campaign_code', 'LIKE', $request->post('campaign_code'))->
                                where('user_id', '=', $request->post('user_id'))->first();
        if($chk){
             return response()->json(['status'=>'error',  'message'=> 'User Already Assigned']);
        } else {
            $postData = $request->post();
            if(is_array($postData['user_id'])){
                $count = 0;
                foreach($postData['user_id'] as $user_id){
                    $input[$count]['user_id'] = $user_id;
                    $input[$count]['campaign_code'] = $postData['campaign_code'];
                    $input[$count]['created_at'] = date('Y-m-d H:i:s');
                    $input[$count]['created_by'] = auth()->id();
                    $count = $count+1;
                }
            } else {
                $input['user_id'] = $postData['user_id'];
                $input['campaign_code'] = $postData['campaign_code'];
                $input['created_at'] = date('Y-m-d H:i:s');
                $input['created_by'] = auth()->id();
            }
            
            $insert = QNAMgmtAssignCampaign::insert($input);
            if($insert){
                 return response()->json(['status'=>'success', 'data'=>$insert,  'message'=> 'Campaign assigned successfully to the user!']);
            } else {
                 return response()->json(['status'=>'error',  'message'=> 'Unable To Assigned Campaign']);
            }
        }
    }
    
    public function userListForCampaign(Request $request){
        $companyTablePrefix = session('company_table_name');
          $validator = Validator::make($request->all(), [
            'campaign_code' => 'required'
        ]);
   
        if($validator->fails()){
	        return response()->json(['status'=>'error', 'message'=> $validator->errors()]);
        }
        
        $campaign = QNAMgmtCampaign::where('campaign_code', 'LIKE', $request->post('campaign_code'))->first()->toArray();
         //print_r(json_decode($campaign['other_parameter'],true));exit;
        $other_paramtere = json_decode($campaign['other_parameter'],true);
        //print_r($other_paramtere['roles']);exit;
        
        if($campaign){
             //DB::enableQueryLog();
            //  $join = QNAMgmtAssignCampaign::select('*')->groupBy('user_id');
                        $code = $request->post('campaign_code');
            $users = DB::table('users as u')
                        ->select(['u.id as user_id', 'u.name', 'u.email', 'u.mobile', 'r.role_name', 'r.slug', 'ur.role_id'])
                        ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
                        ->join('roles as r', 'r.id', '=', 'ur.role_id')
                        // ->join('assign_campaign as ac', 'u.id', '=', 'ac.user_id', 'outter')
                        // //->where('u.id', '!=', 'ac.user_id')
                        // ->where('ac.campaign_code', 'NOT LIKE', $request->post('campaign_code'))
                        // ->where()
                        ->whereNotIn('u.id', DB::table('users')->select('users.id')->join($companyTablePrefix.'assign_campaign as ac', 'users.id', '=', 'ac.user_id')
                        // //->where('u.id', '!=', 'ac.user_id')
                        ->where('ac.campaign_code', 'LIKE', $code)->groupBy('users.id'))  
                        ->whereIn('r.slug', $other_paramtere['roles'])
                        ->GroupBy('u.id')
                        ->where('u.id' , '!=', auth()->id())
                        ->where('u.is_active', 1)->get();
                        
            //print_r(DB::getQueryLog());exit;
            if($users){
                return response()->json(['status'=>'success', 'data'=>$users,  'message'=> 'Users List']);
            } else {
                 return response()->json(['status'=>'error',  'message'=> 'No user available!']);
            }
        } else {
             return response()->json(['status'=>'error',  'message'=> 'Invalid Campaign Detail']);
        }
    }
    
   
}
