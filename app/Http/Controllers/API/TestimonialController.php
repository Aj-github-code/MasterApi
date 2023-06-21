<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\Testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Helpers\Helper as Helper;

class TestimonialController extends Controller
{
    public function getTestimonialLists(Request $request){
        $postData = $request->all();
        try {
            $query = Testimonials::select('*');
                //->where('t.is_active', '=', '1')
                /*if(isset($postData['company_id']) && (NULL !== $postData['company_id'])){
                        $query->where('t.company_id', '=', $postData['company_id']);
                    }*/
                $testimonials = $query->get();
            
            if($testimonials){
                $companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }
                foreach($testimonials as $tkey => $value){
                    $testimonials[$tkey]->description = strip_tags($value->description);
                    $testimonials[$tkey]->image = URL('/').'/public/upload/'.$companyName.'testimonial/'.$value->image;
                    // $testimonials[$tkey]->image = URL('/').'/public/upload/testimonial/'.$value->image;
                }
                return response()->json(['status'=>'success','data'=>$testimonials]);
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
    
    public function getTestimonialList(Request $request){
       //echo 'Testimonial List';exit; 
       //echo "<pre>"; print_r($request->all());exit;
       $company = Helper::getCompany();
       try {
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'testimonials as t')
            ->where('t.is_active', 'LIKE', '1');
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
               $searchQuery = " (t.title like '%".$searchValue."%')";
            }
            
            if((isset($postData['title'])) && (!empty($postData['title']))){
                $query->where('t.title','LIKE',$postData['title']);
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
        
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
            // "image"=>URL('/').'/public/upload/testimonial/'.$record->image,
        foreach($records as $recordKey => $record ){
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "title"=>$record->title,
            "description"=>strip_tags($record->description),
            "image"=>URL('/').'/public/upload/'.$companyName.'testimonial/'.$record->image,
            "priority"=>$record->priority,
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
        
        //return response()->json($response);
        //exit;
        
            return response()->json(['status'=>'success','data'=>$response]);
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    
    public function createAndUpdateTestimonial(Request $request)
    {
        //echo "hello";exit;
        //echo  "<pre>";print_r($request->all());exit;
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            //'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        
        
        $input = $request->all();
        
        if(isset($input['description']) && (NULL !== $input['description'])){
            $input['description'] = strtolower($input['description']);
        } else {
            $input['description'] = '';
        }
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
            $postData['modified_by'] = auth()->id();
            $postData['modified'] = date('Y-m-d H:i:s');
            $response = 'updated';
        } else {
            $postData['id'] = '';
            $postData['created_by'] = auth()->id();
            $postData['created'] = date('Y-m-d H:i:s');
            $response = 'created';
            
        }
        
         $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        //echo  "<pre>";print_r($postData);print_r($response);exit;
        $postData['title'] = $input['title'];
        $postData['description'] = (NULL !== $input['description'])?$input['description']:NULL;
        $postData['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
        $postData['priority'] = (NULL !== $input['priority'])?$input['priority']:'1';
        //upload Image start
            if($request->hasFile('image')){
                // $validator = Validator::make($request->all(), [
                //     'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // ]);
                $original_filename = $request->file('image')->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $request->file('image')->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                	
                    // if ($request->file('image')->move('./public/upload/testimonial/', $image)) {
                        if ($request->file('image')->move('./public/upload/'.$companyName.'testimonial/', $image)) {
                        $postData['image'] = $image;
                    } else {
                        //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                        // return $this->sendError('Cannot upload file');
                    }
                }else{
                    // return $this->sendError('Enter Valid File Format');
                    //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
                }
                //Upload Image end
                // if($validator->fails()){
                //     return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
                // }
            }
        
        
        
  
        try {
            $post = Testimonials::updateOrCreate(['id'=>$postData['id']],$postData);
            
            if($post){
                
                     return response()->json([ 'status' => "success",'message' => "Testimonial ".$response." Successfully",'data'=>$post]);
                

            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Testimonial.']);
            }
            // try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function getTestimonialDetail(Request $request, $id){
        //echo 'Testimonial Detail';exit; 
        $testimonial = Testimonials::where('id',$id)->get();
        if($testimonial){
            return response()->json(['status'=>'success','message'=>'Testimonial Data', 'data'=>$testimonial]);
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Found']);
        }
    }
    
    public function deleteTestimonial(Request $request, $id){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Testimonials::where('id', $id)->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Testimonial '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Testimonial']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
}
