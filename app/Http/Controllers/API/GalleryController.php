<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper as Helper;
use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
class GalleryController extends Controller
{
    
    public function index(Request $request)
    {
            //   echo "hii";exit;
        
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        try {
           $query = gallery::select('*');
           
            
         

            $searchQuery = '';
            
            // print_r($stalls->toSql());exit;
            $searchQuery = ' 1 = 1';
            $filterQuery = '';
            $filterQuery = ' 1 = 1';
            
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
                $filter = (isset($postData['filter']) ?  $postData['filter'] : ""); // filter
                
                if($searchValue != ''){
                   $searchQuery = " (title like '%".$searchValue."%' or is_active like '%".$searchValue."%')";
                }
                if($filter != 'All' && $filter != ''){
                   $filterQuery = " (title like '%".$filter."%')";
                }
                /*if($filter == 'All'){
                    $filter = '';
                   $filterQuery = " (title like '%".$filter."%')";
                }
                if($filter == ''){
                   $filterQuery = "";
                }*/
               /* if(isset($postData['title']) && (NULL !== $postData['title'])){
                    $query->where('title', '=', $postData['title']);
                } 
                */
                if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
                     $query->where('is_active', '=', $postData['is_active']);
                }else {
                     $query->where('is_active', 'LIKE', '1');
                }
            }
            
           
                $query->get();
                $sql = $query;
                $records = $sql->count();
                $totalRecords = $records;
                 //echo $totalRecords;exit;
                
                $totalRecordwithFilter = $query->whereRaw($filterQuery)->count();
                $sql3 = $query->whereRaw($filterQuery)
                //->orderBy('created_at', 'desc')
                ->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                //echo $sql3->toSql();exit;
                $records = $sql3->get();
             if($records){
                 foreach($records as $key => $value){
                      if(Helper::getCompany()){
                        $company = Helper::getCompany().'files/';
                    }
                    // $value->images = URL('/public/upload/'.$company.'gallery/').'/'.$value->images;
                     
                     $records[$key]->sr_no = ($key+1);
                     $records[$key]->images = URL('/public/upload/'.$company.'gallery/').'/'.$value->images;
                    
                    // $records[$key]->images = URL('/public/upload/gallery/').'/'.$value->images;
                     
                 }
                 $response = array(
                   "draw" => intval($draw),
                   "iTotalRecords" => $totalRecordwithFilter,
                   "iTotalDisplayRecords" => $totalRecords,
                   "aaData" => $records
                 );
                return response()->json(['status'=>'success','data'=>$response]);
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
    
    
    public function getGalleryList(Request $request){
        $input = $request->all();

        try {
            $query = gallery::where('is_active', '=', '1');
            if(isset($input['filter']) && (NULL !== $input['filter'])){
                $filterQuery = '';
                $filterQuery = ' 1 = 1';
                if($input['filter'] != 'All' && $input['filter'] != ''){
                   //$filterQuery = " (title like '%".$input['filter']."%')";
                    $query->where('title', '=', $input['filter']);
                }
               
            } 
            $galleries = $query->get();
            $dataList= [];
            if($galleries){
                if(Helper::getCompany()){
                        $company = Helper::getCompany().'files/';
                    }
                    else{
                        $company ='';
                    }
               
                foreach($galleries as $tkey => $value){
                    //print_r($value->images);exit;
                    $value->images = URL('/public/upload/'.$company.'gallery/').'/'.$value->images;
                     $value->slug = base64_encode( $value->id);
                    // $value->images = URL('/public/upload/gallery/').'/'.$value->images;
                    
                    $dataList[$value->title][] = $value;
                     
                     //$dataList[$value->title]->sr_no = ($tkey+1);
                    //  $dataList[$value->title]['images'] = URL('/public/upload/gallery/').'/'.$value->images;
                  
                    // $galleries[$tkey]->images = URL('/').'/public/upload/gallery/'.$value->images;
                }
                return response()->json(['status'=>'success','data'=>$dataList]);
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

    public function createAndUpdateGallery(Request $request){
        $input = $request->all();
        //return response()->json($input);
        $post = array();
        if($request->hasFile('images')){
             $files = $request->file('images');
            //  print_r($files);exit;
                    //   return response()->json($request->file('images'));
        //echo "<pre>"; print_r($input);exit;
                      //echo "<pre>";
                    foreach($request->file('images') as $other_images){
                        $postData = [];
                        $postData['images'] = [];
                       
                            //$res = 'Created';
                            //$postData['other_images']['created'] = date('Y-m-d H:i:s');
                    
                            $original_filename = $other_images->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $other_images->getMimeType();
                            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                          
                                if(Helper::getCompany()){
                                  $company = Helper::getCompany().'files/';
                                }else{
                                    $company ='';
                                }
                            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                            	
                                if ($other_images->move('./public/upload/'.$company.'gallery/', $image)) {
                            
                                    $postData['images'] = $image;
                                
                                } else {}
                            } else {}
                       
                        
                        if(isset($input['title']) && !empty($input['title'])){
                            $postData['title'] = $input['title'];
                        }else{
                            $postData['title'] = $original_filename_arr[0];
                        }
                        $postData['slug'] = Str::slug($postData['images']);
                        $postData['is_active'] = isset($input['is_active']) ? $input['is_active'] : 1;
                        if(isset($input['slug']) && !empty($input['slug'])){
                            $res = 'Updated';
                            $postData['modified_at'] = date('Y-m-d H:i:s');
                            $postData['modified_by'] = auth()->user()->id;
                          
                        } else {
                            $res = 'Created';
                            $postData['created_at'] =  $postData['modified_at'] = date('Y-m-d H:i:s');
                            $postData['created_by'] = $postData['modified_by'] = auth()->user()->id;
                            $input['slug'] = '';
                            DB::enableQueryLog();
                        }
                        
                        
                        //print_r($postData);
                       $post['gallery'][] = Gallery::updateOrCreate(['slug'=>$input['slug']], $postData);
                        //print_r($post['gallery']);
                        
                    }//exit;
 
            if($post){
                return response()->json(['status'=>'success', 'message'=> 'Gallery '.$res.' Successfully', 'data'=>$post['gallery']]);
            }else{
                return response()->json(['status'=>'error', 'message'=> 'Gallery Not Created']);
            }
        } else {
             return response()->json(['status'=>'error', 'message'=> 'Please Upload Images']);
        }
        
        
        
            //$gallery = gallery::updateOrCreate(['id'=>$input['id']], $postData);
           //echo "<pre>"; print_r($post);exit;
     
    }

    public function deleteGallery(Request $request){

        $input = $request->all();
        $postData['is_active'] = isset($input['is_active']) ? $input['is_active'] : '0';
        if(isset($input['slug']) && !empty($input['slug'])){
            $res = 'Updated';
            $postData['modified_at'] = date('Y-m-d H:i:s');
            $postData['modified_by'] = auth()->user()->id;
            $gallery = gallery::updateOrCreate(['id'=>base64_decode($input['slug'])], $postData);
        }
        if($gallery){
            return response()->json(['status'=>'success', 'message'=> 'Gallery '.$res.' Successfully', 'data'=>$gallery]);
        }else{
            return response()->json(['status'=>'error', 'message'=> 'Gallery Not Created']);
        }
    }
}
