<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\Slider;
use App\Models\Sliderdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper as Helper;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
class SliderController extends Controller
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
    
    public function getSliderList(Request $request)
    {
        $this->company = Helper::getCompany();
//  session('company_table_name');
        //echo "<pre>"; print_r($request->all());exit;
        $postData = $request->all();
        try {
            //DB::enableQueryLog();
            $sliderList = Slider::where('is_active', 'LIKE', '1')->with('sliderDetails')->get();
            
  //          echo "<pre>"; print_r($this->company);exit;
            $query =  DB::table($this->company.'sliders as s')
                ->select('s.*','sd.filter_text','sd.type','sd.title_1','sd.title_2','sd.short_description','sd.image','sd.priority','sd.link')
                ->join($this->company.'slider_details as sd', 's.id', '=', 'sd.slider_id', 'left');
                if(NULL!==$request->post('slider_code')){
                    $query->where('s.slider_code', $request->post('slider_code'));
                }
                $query->where('s.is_active', 'LIKE', '1')
                ->where('sd.is_active', 'LIKE', '1')
                //->orderBy('sd.created', 'desc')
                ->orderBy('sd.priority', 'asc');
            $sliders = $query->get();
            //print_r(DB::getQueryLog());exit;

            if($sliders){
                /*$companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }*/
               foreach($sliders as $skey => $value){
                   //print_r($value);
                    if($value->image!=""){
                        $path = $this->companyFilePath.'/sliders/'.$value->image;
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = @file_get_contents($path);
                        if($data !== FALSE){
                            $sliders[$skey]->image = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                        }else{
                            $sliders[$skey]->image = "";
                        }
                        
                    }
                    //$sliders[$skey]->image = URL('/').'/public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/sliders/'.$value->image;
                    // $sliders[$skey]->image = URL('/').'/public/upload/sliders/'.$value->image;
               }//exit;
                return response()->json(['status'=>'success','data'=>$sliders, 'data2'=>$sliderList]);
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
    
    public function getSliders(Request $request)
    {
        //echo "<pre>"; print_r($request->all());exit;
        
        //$postData['slider']['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
        $postData = $request->all();
        
        try {
        $company = Helper::getCompany();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
            $query = DB::table($company.'sliders as s')
                ->select('s.*')
                ->join($company.'slider_details as sd', 's.id', '=', 'sd.slider_id', 'left');
                if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
                    $query->where('sd.is_active', 'LIKE', $postData['is_active']);
                } 
                $query->groupBy('s.id')->orderBy('sd.priority', 'asc');
            /*$query = $query->groupBy('id')
                    ->get();*/
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
               $searchQuery = " (s.name like '%".$searchValue."%')";
            }
            
            if((isset($postData['name'])) && (!empty($postData['name']))){
                $query->where('s.name','LIKE',$postData['name']);
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
             
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "slider_code"=>$record->slider_code,
            "js"=>$record->js,
            //"image_count"=>$record->image_count,
            "css"=>$record->css,
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
       
        return response()->json(['status'=>'success','data'=>$response]);
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function createAndUpdateSlider(Request $request)
    {
        //echo "hello";exit;
        //echo  "<pre>";print_r($request->all());exit;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
          
            // 'slider_code' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $input = $request->all();
        
        if(isset($input['slider_code']) && (NULL !== $input['slider_code'])){
            $input['slider_code'] = strtolower(str_replace(' ', '-', $input['slider_code']));
        } else {
            $input['slider_code'] = $this->createSliderCode($input['name']);
        }
        
        
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['slider']['id'] = $input['id'];
        } else {
            $postData['slider']['id'] = '';
        }
        
        $postData['slider']['name'] = $input['name'];
        $postData['slider']['js'] = (NULL !== $input['js'])?$input['js']:NULL;
        $postData['slider']['slider_code'] = $input['slider_code'];
        $postData['slider']['css'] = (NULL !== $input['css'])?$input['css']:NULL;
        $postData['slider']['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
        
        $postData['slider']['created'] = date('Y-m-d H:i:s');
        //$postData['slider']['created_by'] = auth()->id();
        // $postData['slider_details']['created'] = date('Y-m-d H:i:s');
        //$postData['slider']['created_by'] = auth()->id();
        $response = response()->json([
            'status' =>  "success",
            'message' => "Slider created Successfully"
        ],200);
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        try {
            $post['slider'] = Slider::updateOrCreate(['id'=>$postData['slider']['id']],$postData['slider']);
            // print_r($post['slider']['id']);exit;
        
            if($post['slider']){
                if(isset($input['slider_details']) && (NULL !== $input['slider_details'])){
                    foreach($input['slider_details'] as $key => $sliderDetails){
                        
                        if($request->hasFile('slider_details.'.$key.'.image')){
                            $original_filename = $request->file('slider_details.'.$key.'.image')->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $request->file('slider_details.'.$key.'.image')->getMimeType();
                            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                            	
                                // if ($request->file('slider_details.'.$key.'.image')->move('./public/upload/sliders/', $image)) {
                                if ($request->file('slider_details.'.$key.'.image')->move('./public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/sliders/', $image)) {
                                    $postData['slider_details']['image'] = $image;
                                } else {
                                    //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                                    // return $this->sendError('Cannot upload file');
                                }
                            }else{
                                // return $this->sendError('Enter Valid File Format');
                                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
                            }
                        }
                        
                        if(isset($sliderDetails['id']) && (NULL !== $sliderDetails['id'])){
                            $postData['slider_details']['id'] = $sliderDetails['id'];   
                        } else {
                            $postData['slider_details']['id'] = '';
                        }
                        $postData['slider_details']['slider_id'] = $post['slider']['id'];
                        $postData['slider_details']['type'] = isset($sliderDetails['type'])?$sliderDetails['type']:'';
                        $postData['slider_details']['title_1'] = isset($sliderDetails['title_1'])?$sliderDetails['title_1']:'';
                        $postData['slider_details']['title_2'] = isset($sliderDetails['title_2'])?$sliderDetails['title_2']:'';
                        $postData['slider_details']['short_description'] = isset($sliderDetails['short_description'])?$sliderDetails['short_description']:'';
                        $postData['slider_details']['priority'] = isset($sliderDetails['priority'])?$sliderDetails['priority']:'';
                        $postData['slider_details']['link'] = isset($sliderDetails['link'])?$sliderDetails['link']:'';
                        
                        $post['slider_details'][] = Sliderdetail::updateOrCreate(['id'=>$postData['slider_details']['id']],$postData['slider_details']);
                        
                        $postData['slider_details'] = [];
                    }
                        
                    if(count($post['slider_details'])>0){
                        return response()->json(['status'=>'success','message'=>'Slider and Slider Details Created Successfully!','data'=>$post]);
                    } else {
                        return response()->json(['status'=>'success','message'=>'Slider Created Successfully And Slider Detail Not Created!','data'=>$post]);
                    }
                } else {
                     return response()->json(['status'=>'success','message'=>'Slider Created Successfully!','data'=>$post]);
                }

            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Slider.']);
            }
            // try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function getSliderAndDetails(Request $request, $id)
    {
        $slider = Slider::where('slider_code', 'LIKE', $id)->with('sliderDetails')->get();
        if($slider){
            /*$companyName = '';
            if(Helper::getCompany()){
                $companyName = Helper::getCompany().'files/';
            }*/
        
            foreach($slider as $skey => $value){
                //echo "<pre>";print_r($slider[$skey]['sliderDetails'][0]['image']);exit;
                //$slider[$skey]->image = URL('/').'/public/upload/slider/'.$value->image;
                //echo "<pre>";print_r($value->css);
                $sdetail_image = $slider[$skey]['sliderDetails'];
                if(isset($sdetail_image)){
                    foreach($sdetail_image as $sdkey => $sliderdetails){
                        if($sliderdetails['image']!=""){
                            $path = $this->companyFilePath.'/sliders/'.$sliderdetails['image'];
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = @file_get_contents($path);
                            if($data !== FALSE){
                                $sliderDetails[$sdkey]['image'] = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                            }else{
                                $sliderDetails[$sdkey]['image'] = "";
                            }
                            
                        }
                        //$sliderDetails[$sdkey]['image'] = URL('/').'/public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/sliders/'.$sliderdetails['image'];
                        // $sliderDetails[$sdkey]['image'] = URL('/').'/public/upload/sliders/'.$sliderdetails['image'];
                    // echo "<pre>";print_r($sliderDetails[$sdkey]['image']);
                    $slider[$skey]['sliderDetails'][$sdkey]['image'] = $sliderDetails[$sdkey]['image'];
                    }
                }
                
                
            }
                
                // echo "<pre>";print_r($sliderDetails['image']);
                // exit;
            return response()->json(['status'=>'success','message'=>'Slider Data', 'data'=>$slider]);
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Found']);
        }
    }
    
    public function deleteSlider(Request $request, $id)
    {
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Slider::where('id', $id)->update(['is_active' => $is_active]);
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Slider '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Slider']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function deleteSliderDetail(Request $request, $id)
    {
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Sliderdetail::where('id', $id)->update(['is_active' => $is_active]);
            if($delete){
                
                 return response()->json(['status'=>'success','message'=>'Slider '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Slider']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function createSliderCode($name = "slider")
    {
        
        $name = strtolower(str_replace(' ', '-', $name));
        $sliderCount = Slider::where('name', 'LIKE', $name.'%')->count();
        $count = '0001';
        if($sliderCount < 9999 && ($sliderCount > 1000)){
            $count = $sliderCount+1;
        } else if($sliderCount < 999 && ($sliderCount > 100)){
            $count = '0'.($sliderCount+1);
        } else if($sliderCount < 99 && ($sliderCount > 10)){
            $count = '00'.($sliderCount+1);
        } else {
            $count = '000'.($sliderCount+1);
        }
        return $name.'-'.$count;
    }
    
    public function getBackendSliderList(Request $request)
    {
        $this->company = session('company_table_name');
        //echo "<pre>"; print_r($request->all());exit;
        $postData = $request->all();
        try {
            $sliderList = Slider::where('is_active', 'LIKE', '1')->with('sliderDetails')->get();
            
            //echo "<pre>"; print_r($slidersList);exit;
            $query =  DB::table($this->company.'sliders as s')
                ->select('s.*','sd.filter_text','sd.type','sd.title_1','sd.title_2','sd.short_description','sd.image','sd.priority','sd.link')
                ->join($this->company.'slider_details as sd', 's.id', '=', 'sd.slider_id', 'left');
            if(NULL!==$request->post('slider_code')){
                $query->where('slider_code', $request->post('slider_code'));
            }
                $query->where('s.is_active', 'LIKE', '1')
                ->where('sd.is_active', 'LIKE', '1')
                //->orderBy('sd.created', 'desc')
                ->orderBy('sd.priority', 'asc');
            $sliders = $query->get();
           //echo "<pre>"; print_r($sliders);exit;
            if($sliders){
                /*$companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }*/
               foreach($sliders as $skey => $value){
                   //print_r($value);
                    if($value->image!=""){
                        $sliders[$skey]->image = $this->companyFilePath.'/sliders/'.$value->image;
                        /*$path = $this->companyFilePath.'/sliders/'.$value->image;
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = @file_get_contents($path);
                        if($data !== FALSE){
                            $sliders[$skey]->image = 'data:image/' . $type . ';base64,' . base64_encode($data);    
                        }else{
                            $sliders[$skey]->image = "";
                        }*/
                        
                    }
                    //$sliders[$skey]->image = URL('/').'/public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/sliders/'.$value->image;
                    // $sliders[$skey]->image = URL('/').'/public/upload/sliders/'.$value->image;
               }//exit;
                return response()->json(['status'=>'success','data'=>$sliders, 'data2'=>$sliderList]);
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
    
        public function getadminSliderAndDetails(Request $request, $id)
    {
        $slider = Slider::where('slider_code', 'LIKE', $id)->with('sliderDetails')->get();
        if($slider){
        
            foreach($slider as $skey => $value){
                //echo "<pre>";print_r($slider[$skey]['sliderDetails'][0]['image']);exit;
                //$slider[$skey]->image = URL('/').'/public/upload/slider/'.$value->image;
                //echo "<pre>";print_r($value->css);
                $sdetail_image = $slider[$skey]['sliderDetails'];
                if(isset($sdetail_image)){
                    foreach($sdetail_image as $sdkey => $sliderdetails){
                        if($sliderdetails['image']!=""){
                            $path = $this->companyFilePath.'/sliders/'.$sliderdetails['image'];
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = @file_get_contents($path);
                            if($data !== FALSE){
                                $sliderDetails[$sdkey]['image'] = $path;    
                            }else{
                                $sliderDetails[$sdkey]['image'] = "";
                            }
                            
                        }
                        //$sliderDetails[$sdkey]['image'] = URL('/').'/public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/sliders/'.$sliderdetails['image'];
                        // $sliderDetails[$sdkey]['image'] = URL('/').'/public/upload/sliders/'.$sliderdetails['image'];
                    // echo "<pre>";print_r($sliderDetails[$sdkey]['image']);
                    $slider[$skey]['sliderDetails'][$sdkey]['image'] = $sliderDetails[$sdkey]['image'];
                    }
                }
                
                
            }
                
                // echo "<pre>";print_r($sliderDetails['image']);
                // exit;
            return response()->json(['status'=>'success','message'=>'Slider Data', 'data'=>$slider]);
        } else {
            return response()->json(['status'=>'error','message'=>'No Data Found']);
        }
    }
}

