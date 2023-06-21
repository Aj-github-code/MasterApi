<?php

namespace App\Http\Controllers\API;

use App\Models\tags;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

class TagController extends Controller
{
    //
    public function index(Request $request){
        $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('tags')->select(['tags.*']);
                   
                   
            $searchQuery = '';
            // print_r($query);exit;
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
                   $searchQuery = " (name like '%".$searchValue."%' or slug like '%".$searchValue."%')";
                }
            }
            
            $query->get();


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
            //   $response = array(
            //       "draw" => intval($draw),
            //       "iTotalRecords" => $totalRecordwithFilter,
            //       "iTotalDisplayRecords" => $totalRecords,
            //       "aaData" => $records
            //     );
             if($records){
                 /*foreach($records as $key => $value){
                    //if(NULL !== $value->images){
                        $records[$key]['description'] = strip_tags($value->description);
                    //}
                
                 }*/
                return response()->json(['status'=>'success','data'=>$records]);
            }else{
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
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
   
        $input = $request->all();
       
        $postData['name'] = $input['name'];
        if(isset($input['slug']) && (NULL !== $input['slug'])){
            $postData['slug'] = $input['slug'];
        }else{
            $postData['slug'] = Str::slug($input['name']);
        }
        $postData['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['created_by'] = auth()->user()->id;
       
        
        // print_r($postData);exit;
        
        $checkpostData = DB::table('tags')->select('*')->where(['slug'=> $input['slug']])->count();
        if($checkpostData > 0){
            $res = 'Update';
            $insert['modified_at'] = date('Y-m-d H:i:s');
            $insert['modified_by'] = auth()->user()->id;
        }else{
            $res = 'Inserted'; 
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['created_by'] = auth()->user()->id;
        }
            $post = tags::updateOrCreate(['slug'=>$postData['slug']],$postData);

            if($post){
                return response()->json([
                    'status' => 'success',
                    'message' => "Tags ".$res." Successfully",
                    'data' => $post
                ]);
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
    
    
    public function delete(Request $request){
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        //echo "<pre>"; print_r($postData['is_active']);exit;
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = tags::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Tag '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Feature']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
}
