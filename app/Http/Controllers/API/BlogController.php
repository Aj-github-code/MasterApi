<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\BlogCategories;
use App\Models\Blogs;
use App\Models\BlogsCategories;
use App\Models\BlogPosts;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Helpers\Helper as Helper;

class BlogController extends Controller
{
    //
    public function list(Request $request){
         $company = Helper::getCompany();
         $category = array();
         $category_detail = array();
       try {
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'blogs as bl')
            ->where('bl.is_active', 'LIKE', '1');
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
               $searchQuery = " (bl.title like '%".$searchValue."%')";
            }
            
            if((isset($postData['title'])) && (!empty($postData['title']))){
                $query->where('bl.title','LIKE',$postData['title']);
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
            $companyName = Helper::getCompanyDir().'files/';
        }
        foreach($records as $recordKey => $record ){
            $categoryList = BlogsCategories::select('category_id')->where('blog_id', $record->id)->get();
            foreach($categoryList as $clKey => $clRecord){
                $category_name = BlogCategories::select('id', 'title', 'slug')->where(['id'=>$clRecord['category_id']])->first();
                //$category[] = $category_name['title'];
                $category[] = $category_name['id'];
                $category_detail[$clKey]['id'] = $category_name['id'];
                $category_detail[$clKey]['category_title'] = $category_name['title'];
                $category_detail[$clKey]['category_slug'] = $category_name['slug'];
               // print_r($cateogry_detail);
            }//exit;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "title"=>$record->title,
            "slug"=>$record->slug,
            "description"=>strip_tags($record->description),
            "image"=>URL('/').'/public/upload/'.$companyName.'blog/'.$record->image,
            "banner_image"=>URL('/').'/public/upload/'.$companyName.'blog/'.$record->banner_image,
            "category_id"=>$category,
            "cateory_detail"=>$category_detail,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
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
    
    public function create(Request $request){
        $input = $request->all();
        $data = array();
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
            $postData['modified_by'] = auth()->id();
            $postData['modified_at'] = date('Y-m-d H:i:s');
            $response = 'updated';
        } else {
            $postData['id'] = '';
            $postData['created_by'] = auth()->id();
            $postData['created_at'] = date('Y-m-d H:i:s');
            $response = 'created';
            
        }
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompanyDir().'files/';
        }
        //print_r($companyName);exit;
            $postData['title'] = $input['title'];
            if(isset($input['slug']) && (NULL !== $input['slug'])){
                $postData['slug'] = $input['slug'];
            }else{
                $postData['slug'] = Str::slug($input['title']);
            }
            $postData['description'] = (NULL !== $input['description'])?$input['description']:NULL;
            $postData['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
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
                	
                        if ($request->file('image')->move('./public/upload/'.$companyName.'blog/', $image)) {
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
                
            }
            
            if($request->hasFile('banner_image')){
                // $validator = Validator::make($request->all(), [
                //     'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // ]);
                $original_filename = $request->file('banner_image')->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $request->file('banner_image')->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                	$bannerimage = $original_filename_arr[0].time(). '.'.$file_ext;
                	
                        if ($request->file('banner_image')->move('./public/upload/'.$companyName.'blog/', $bannerimage)) {
                        $postData['banner_image'] = $bannerimage;
                    } else {
                        //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                        // return $this->sendError('Cannot upload file');
                    }
                }else{
                    // return $this->sendError('Enter Valid File Format');
                    //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
                }
                //Upload Image end
                
            }
        try{
            //return $postData;
            //DB::enableQueryLog();
            $post = Blogs::updateOrCreate(['id'=>$postData['id']],$postData);
            //return $post;
            //return DB::getQueryLog();
            if($post){
                $data['title'] = $post['title'];
                 $data['slug'] = $post['slug'];
                 $data['description'] = $post['description'];
                 $data['image'] =$post['image'];
                 $data['baner_image'] = $post['baner_image'];
                 $data['is_active'] = $post['is_active'];
                foreach($input['category_id'] as $cidkey => $cidvalue){
                    //return $cidvalue;
                    $postDataBlogsCategories[$cidkey]['blog_id'] = $post['id'];
                    $postDataBlogsCategories[$cidkey]['category_id'] = (int)$cidvalue;
                    //DB::enableQueryLog();
                    $checkBlogsCategories = BlogsCategories::select('*')->where(['blog_id'=>$post['id'],'category_id'=>$postDataBlogsCategories[$cidkey]['category_id']])->get();
                    
                    if(count($checkBlogsCategories)>0){
                        $postBlogsCategories = BlogsCategories::updateOrCreate(['blog_id'=>$checkBlogsCategories[0]->blog_id,'category_id'=>$checkBlogsCategories[0]->category_id],$postDataBlogsCategories);
                    }else{
                        $postBlogsCategories = BlogsCategories::create(['blog_id'=>$post['id'],'category_id'=>$postDataBlogsCategories[$cidkey]['category_id']],$postDataBlogsCategories);
                    }    
                    $category_name = BlogCategories::select('title')->where(['id'=>$postBlogsCategories['category_id']])->first();
                    $data['category_id'][] = $category_name['title'];
                }
                return response()->json([ 'status' => "success",'message' => "Blog ".$response." Successfully",'data'=>$data]);
            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Blog.']);
            }
        }catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error[1]]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
    }
    
    public function delete(Request $request){
        //echo 'Blog Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Blogs::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Blog '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Blog']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function view(Request $request, $slug){
        //if($_SERVER['REQUEST_METHOD']=='POST'){
            DB::enableQueryLog();
            $company = '';
            if(Helper::getCompany()){
                $company = Helper::getCompany();
                
            }
            //$postData = $request->post();
            $blog = DB::table($company.'blogs as bl')->select('bl.*', 'bc.slug as category_slug', 'bc.title as category_name')
            ->join($company.'blogs_categories as bsc', 'bsc.blog_id','=','bl.id', 'left')
            ->join($company.'blog_categories as bc', 'bc.id','=','bsc.category_id', 'left')
            ->where('bl.slug', 'LIKE', $slug)
            ->where('bl.is_active','1')
            ->first();
            //return (DB::getQueryLog());
            return response()->json(['status'=>'success','data'=>$blog]);
            
        //}
    }
    
    public function categoryList(Request $request){
         $company = Helper::getCompany();
       try {
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'blog_categories as bc')
            ->where('bc.is_active', 'LIKE', '1');
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
               $searchQuery = " (bc.title like '%".$searchValue."%')";
            }
            
            if((isset($postData['title'])) && (!empty($postData['title']))){
                $query->where('bc.title','LIKE',$postData['title']);
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
        foreach($records as $recordKey => $record ){
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "title"=>$record->title,
            "slug"=>$record->slug,
            "description"=>strip_tags($record->description),
            "image"=>URL('/public/upload/'.$companyName.'blog_category/').'/'.$record->image,
            "banner_image"=>URL('/').'/public/upload/'.$companyName.'blog_category/'.$record->banner_image,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
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
    
    public function categoryCreate(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
            $postData['modified_by'] = auth()->id();
            $postData['modified_at'] = date('Y-m-d H:i:s');
            $response = 'updated';
        } else {
            $postData['id'] = '';
            $postData['created_by'] = auth()->id();
            $postData['created_at'] = date('Y-m-d H:i:s');
            $response = 'created';
            
        }
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        //print_r($companyName);exit;
            $postData['title'] = $input['title'];
            if(isset($input['slug']) && (NULL !== $input['slug'])){
                $postData['slug'] = $input['slug'];
            }else{
                $postData['slug'] = Str::slug($input['title']);
            }
            $postData['description'] = (NULL !== $input['description'])?$input['description']:NULL;
            $postData['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
            $postData['created_at'] = date('Y-m-d H:i:s');
            $postData['created_by'] = auth()->user()->id;
            
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
                        if ($request->file('image')->move('./public/upload/'.$companyName.'blog_category/', $image)) {
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
                
            }
            
            if($request->hasFile('banner_image')){
                // $validator = Validator::make($request->all(), [
                //     'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // ]);
                $original_filename = $request->file('banner_image')->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $request->file('banner_image')->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                	$bannerimage = $original_filename_arr[0].time(). '.'.$file_ext;
                	
                    // if ($request->file('image')->move('./public/upload/testimonial/', $image)) {
                        if ($request->file('banner_image')->move('./public/upload/'.$companyName.'blog_category/', $bannerimage)) {
                        $postData['banner_image'] = $bannerimage;
                    } else {
                        //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                        // return $this->sendError('Cannot upload file');
                    }
                }else{
                    // return $this->sendError('Enter Valid File Format');
                    //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
                }
                //Upload Image end
                
            }
        try{
           // DB::enableQueryLog();
            $post = BlogCategories::updateOrCreate(['id'=>$postData['id']],$postData);
                //return response()->json(['status'=>'error','message'=>$error[1],'data'=>DB::getQueryLog()]);
            //return DB::getQueryLog();
            if($post){
                
                     return response()->json([ 'status' => "success",'message' => "Blog Category ".$response." Successfully",'data'=>$post]);
                

            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Blog Category.']);
            }
        }catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error[1]]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
        
    }
    
    public function categoryDelete(Request $request){
         $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = BlogCategories::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Blog Category '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Blog Category']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function categoryView(Request $request, $slug){
            DB::enableQueryLog();
            $blogsData = array();
            $companyName = '';
            if(Helper::getCompany()){
                $companyName = Helper::getCompanyDir();
                
            }
             try {
            //$postData = $request->post();
            $blogCategory = BlogCategories::select('*')->where('slug',$slug)->first();
            //print_r($blogCategory->id);exit;
            
            $blogList = DB::table($company.'blogs as bl')->select('bl.*')
            ->join($company.'blogs_categories as bsc', 'bsc.blog_id','=','bl.id', 'left')
            //->join($company.'blog_categories as bc', 'bc.id','=','bsc.category_id', 'left')
            ->where('bsc.category_id', 'LIKE', $blogCategory->id)
            ->where('bl.is_active','1')
            ->get();
            /*print_r($blogList);
            exit;*/
            foreach($blogList as $bKeys=>$bValue){
                //echo 'vhii';print_r($bValue->title);
                $blogsData[$bKeys]->title = $bValue->title;
                $blogsData[$bKeys]->slug = $bValue->slug;
                $blogsData[$bKeys]->description = $bValue->description;
                $blogsData[$bKeys]->image = URL('/').'/public/upload/'.$companyName.'blog/'.$bValue->image;
                $blogsData[$bKeys]->banner_image = URL('/').'/public/upload/'.$companyName.'blog/'.$bValue->banner_image;
                $blogsData[$bKeys]->is_active = $bValue->is_active;
                $blogsData[$bKeys]->id = $bValue->id;
            }//echo 'hii';
            /*print_r($blogsData);
            exit;*/
            $blogCategoryList->blogs = $blogsData;
            //return (DB::getQueryLog());
            return response()->json(['status'=>'success','data'=>$blogCategoryList]);
            
        }catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function blogCategoryView(Request $request, $slug){
    
        DB::enableQueryLog();
            $blogsData = array();
            $company = '';
            if(Helper::getCompany()){
                $company = Helper::getCompany();
                
            }
             try {
            //$postData = $request->post();
            $blogCategory = BlogCategories::select('*')->where('slug',$slug)->first();
            //print_r($blogCategory->id);exit;
            
            $blogList = DB::table($company.'blogs as bl')->select('bl.*')
            ->join($company.'blogs_categories as bsc', 'bsc.blog_id','=','bl.id', 'left')
            //->join($company.'blog_categories as bc', 'bc.id','=','bsc.category_id', 'left')
            ->where('bsc.category_id', 'LIKE', $blogCategory->id)
            ->where('bl.is_active','1')
            ->get();
            /*print_r($blogList);
            exit;*/
            foreach($blogList as $bKeys=>$bValue){
                //echo 'vhii';print_r($bValue->title);
                $blogsData[$bKeys]['title'] = $bValue->title;
                $blogsData[$bKeys]['slug'] = $bValue->slug;
                $blogsData[$bKeys]['description'] = $bValue->description;
                $blogsData[$bKeys]['image'] = $bValue->image;
                $blogsData[$bKeys]['banner_image'] = $bValue->banner_image;
                $blogsData[$bKeys]['is_active'] = $bValue->is_active;
                $blogsData[$bKeys]['id'] = $bValue->id;
            }//echo 'hii';
            /*print_r($blogsData);
            exit;*/
            $blogCategory->blogs = $blogsData;
            //return (DB::getQueryLog());
            return response()->json(['status'=>'success','data'=>$blogCategory]);
            
        }catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    public function blogPostList(Request $request){
           $company = Helper::getCompany();
           $blogDetail = array();
           $ch_posts = array();
       try {
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $query = DB::table($company.'blog_posts as bpo')
            ->where('bpo.is_active', 'LIKE', '1');
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
               $searchQuery = " (bpo.post like '%".$searchValue."%')";
            }
            
            if((isset($postData['post'])) && (!empty($postData['post']))){
                $query->where('bpp.post','LIKE',$postData['post']);
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
        foreach($records as $recordKey => $record ){
            $blog_title = Blogs::select('title')->where(['id'=>$record->blog_id])->first();
            $blog_title = $blog_title['title'];
            $child_post = BlogPosts::select('*')->where(['parent_id'=>$record->id])->get();
            $ch_posts = $child_post;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "blog_id"=>$record->blog_id,
            "parent_id"=>$record->parent_id,
            "post"=>strip_tags($record->post),
            "blog_title"=>$blog_title,
            "child_posts"=>$ch_posts,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
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
    
    public function blogPostCreate(Request $request){
        $input = $request->all();
        $child_post = array();
        $child_posts = array();
        $validator = Validator::make($request->all(), [
            'post' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        if(isset($input['id']) && (NULL !== $input['id'])){
            $postData['id'] = $input['id'];
            $postData['modified_by'] = auth()->id();
            $postData['modified_at'] = date('Y-m-d H:i:s');
            $response = 'updated';
        } else {
            $postData['id'] = '';
            $postData['created_by'] = auth()->id();
            $postData['created_at'] = date('Y-m-d H:i:s');
            $response = 'created';
            
        }
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        $postData['post'] = $input['post'];
        $postData['blog_id'] = $input['blog_id'];
        $postData['parent_id'] = (NULL !== $input['parent_id'])?$input['parent_id']:'0';
        $postData['is_active'] = (NULL !== $input['is_active'])?$input['is_active']:'1';
        
        try{
           // DB::enableQueryLog();
            $post = BlogPosts::updateOrCreate(['id'=>$postData['id']],$postData);
                //return response()->json(['status'=>'error','message'=>$error[1],'data'=>DB::getQueryLog()]);
            //return DB::getQueryLog();
            if($post){
                  
                $blog_title = Blogs::select('title')->where(['id'=>$post['blog_id']])->first();
                $post['blog_title'] = $blog_title['title'];
                $child_post = BlogPosts::select('*')->where(['id'=>$post['parent_id']])->get();
                foreach($child_post as $cpkey => $cpvalue){
                    $child_posts[] = BlogPosts::select('*')->where(['id'=>$cpvalue['parent_id']])->first();
                    //$category[] = $category_name['title'];
                }
                $post['child_posts'] = $child_posts;
                return response()->json([ 'status' => "success",'message' => "Post ".$response." Successfully",'data'=>$post]);
            } else {
                return response()->json(['status'=>'error','message'=>'Unable To Create Blog.']);
            }
        }catch(\Illuminate\Database\QueryException  $e) {
                $error = explode(':',$e->getMessage());
                return response()->json(['status'=>'error','message'=>$error[1]]);
            } catch(Exception $ex) {
                return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
            }
    }
    
    public function blogPostDelete(Request $request){
        //echo 'Blog Post Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = BlogPosts::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Blog Post '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Blog Post']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
}
