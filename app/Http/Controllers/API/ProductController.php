<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\ProductCategories;
use App\Models\ProductProductCategory;
use App\Models\ProductImages;
use App\Models\setup;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

use App\Models\Vehicles;
use App\Models\VehicleType;
use App\Models\VehicleManufacturer;
use App\Models\VehicleModel;
use App\Models\VehicleVariation;
use App\Models\VehicleFeature;
use App\Helpers\Helper as Helper;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      
        //   echo "hii";exit;
        DB::enableQueryLog();
        $company = Helper::getCompany();
        
        $postData = $request->all();
        try {
           $query = DB::table($company.'products')->select($company.'products.*', 'pc.hsn_code','pi.image_name_1','pi.image_name_2')
            ->join($company.'product_categories as pc', 'pc.id', '=', $company.'products.product_category_id')
            ->join($company.'product_images as pi', 'pi.product_id', '=', $company.'products.id', 'left');
            if(isset($postData['product_category_id']) && NULL !== $postData['product_category_id']){
                $query->where('product_category_id', '=', $postData['product_category_id']);
            } 
            $product = $query->where($company.'products.is_active', 'LIKE', '1')->orderBy($company.'products.priority', 'asc')->orderBy($company.'products.created', 'desc')->get();
            //print_r(DB::getQueryLog());
//print_r($product);exit;
             if($product){
                return response()->json(['status'=>'success','data'=>$product]);
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
    
    public function getProductAndServiceList(Request $request)
    {
        $company = Helper::getCompany();
          //echo "hii";exit;
        
        DB::enableQueryLog();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        
        try {
           $query = DB::table($company.'products as p')->select('p.*', 'pc.hsn_code', 'pc.is_service', 'pc.slug as category_slug')
            //->join($company.'product_images as pi', 'pi.product_id', '=', 'p.id', 'left')
            ->join($company.'product_categories as pc', 'pc.id', '=', 'p.product_category_id','left');
           
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
                   $searchQuery = " (p.product like '%".$searchValue."%' or p.base_price like '%".$searchValue."%')";
                }
                if(isset($postData['product_category']) && (NULL !== $postData['product_category'])){
                    $query->where('pc.category_name', 'LIKE', $postData['product_category']);
                } 
                if(isset($postData['category_slug']) && (NULL !== $postData['category_slug'])){
                    $query->where('pc.slug', '=', $postData['category_slug']);
                }
                if(isset($postData['slug']) && (NULL !== $postData['slug'])){
                    $query->where('p.slug', '=', $postData['slug']);
                } 
                
                if(isset($postData['is_service']) && (NULL !== $postData['is_service'])){
                    $query->where('pc.is_service', '=', $postData['is_service']);
                } else {
                    $query->where('pc.is_service', '=', '0');
                }
                if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
                     if($postData['is_active'] === 'all'){
                         
                     } else {
                        $query->where('p.is_active', '=', $postData['is_active']);
                     }
                }
            }
            
            
                $query->where('p.is_active', 'LIKE', '1')->where('pc.is_active', '=', '1')->get();

                $sql = $query;
                $records = $sql->count();
                $totalRecords = $records;
                //print_r($query);exit;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)
                ->orderBy('p.created', 'desc')
                ->orderBy('p.priority', 'asc')
                ->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                //echo $sql3->toSql();exit;
                $productRecords = $sql3->get();
                //print_r($productRecords);exit;
            //   $response = array(
            //       "draw" => intval($draw),
            //       "iTotalRecords" => $totalRecordwithFilter,
            //       "iTotalDisplayRecords" => $totalRecords,
            //       "aaData" => $records
            //     );
            $companyName = '';
            $productImage = array();
            if(Helper::getCompany()){
                $companyName = Helper::getCompanyDir().'files/';
            }
             if($productRecords){
                 foreach($productRecords as $key => $value){
                     
                     $productRecords[$key]->sr_no = ($key+1);
                      
                     $productRecords[$key]->id = $value->id;
                     //$records[$key]->id = ($key+1);
                     $productRecords[$key]->description = htmlspecialchars_decode($value->description);
                     $productRecords[$key]->banner_image = URL('/public/upload/'.$companyName.'product/').'/'.$value->banner_image;
                     $productRecords[$key]->featured_image = URL('/public/upload/'.$companyName.'product/').'/'.((NULL !== $value->featured_image) ? $value->featured_image : $value->banner_image);
                     $productImage = ProductImages::select('*')->where(['product_id'=>$value->id,'is_active'=>'1'])->get();
                     
                     //$productImage = DB::table($company.'product_images as pi')->select('*')->where(['pi.product_id'=>$value->id,'pi.is_active'=>'1'])->get();
                     foreach($productImage as $piKey=>$piValue){
                         $productImage[$piKey]->image_name_1 = URL('/public/upload/'.$companyName.'product/').'/'.$piValue->image_name_1;
                     }
                     $productRecords[$key]->other_images = $productImage;
                    //  $records[$key]->banner_image = URL('/public/upload/product/').'/'.$value->banner_image;
                    //  $records[$key]->featured_image = URL('/public/upload/product/').'/'.((NULL !== $value->featured_image) ? $value->featured_image : $value->banner_image);
                 }
                $response = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordwithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $productRecords
                );
                return response()->json(['status'=>'success','data'=>$response]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1], 'data'=>DB::getQueryLog()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getProductAndServices(Request $request, $id){
         $postData = $request->all();
         $productImage = array();
           $company = Helper::getCompany();
        $query = product::select([$company.'products.*', 'pc.hsn_code'])->with('productImages')
                            ->join($company.'product_product_categories as ppc', 'ppc.product_id', '=', $company.'products.id')
                            ->join($company.'product_categories as pc', 'pc.id', '=', 'ppc.product_category_id')
                            ->where($company.'products.slug', $id)
                             ->orderBy($company.'products.priority', 'asc')
                             ->orderBy($company.'products.created', 'desc');
                            
            if(isset($postData['type']) && ($postData['type'] === "service")){
                $query->where('pc.is_service', '=', 1);
            } else {
                $query->where('pc.is_service', '=', 0);
            }
            
             $product = $query->where($company.'products.is_active', '=', '1')->first();

             if($product){
                 $companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompanyDir().'files/';
                }
                $product->banner_image = URL('/public/upload/'.$companyName.'product/').'/'.$product->banner_image;
                $product->featured_image = URL('/public/upload/'.$companyName.'product/').'/'.((NULL !== $product->featured_image) ? $product->featured_image : $product->banner_image);
                // $product->banner_image = URL('/public/upload/product/').'/'.$product->banner_image;
                // $product->featured_image = URL('/public/upload/product/').'/'.((NULL !== $product->featured_image) ? $product->featured_image : $product->banner_image);
                $productImage = ProductImages::select('*')->where(['product_id'=>$product->id,'is_active'=>'1'])->get();
                     
                 foreach($productImage as $piKey=>$piValue){
                     $productImage[$piKey]->image_name_1 = URL('/public/upload/'.$companyName.'product/').'/'.$piValue->image_name_1;
                 }
                 $product->other_images = $productImage;
                
                return response()->json(['status'=>'success','data'=>$product]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
    }
    
    public function getServices(Request $request) {
        $company = Helper::getCompany();
        $postData = $request->all();
           $query = DB::table($company.'products')->select([$company.'products.*', 'pc.hsn_code'])
                   ->join($company.'product_categories as pc', 'pc.id', '=', $company.'products.product_category_id');
            
                $query->where('pc.is_service', '=', 1);
        
            $product = $query->where($company.'products.is_active', 'LIKE', '1')->get();


             if($product){
                return response()->json(['status'=>'success','data'=>$product]);
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
    
    public function createProduct(Request $request)
    {
     //echo 'hii';exit;
        $validator = Validator::make($request->all(), [
            'product_category_id' => 'required',
            // 'product_master_id' => 'required',
            'product_type' => 'required',
            'product' => 'required',
            'base_price' => 'required',
            'description' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
   
   
        $input = $request->all();
        
        //$postData['product_master_id'] = isset($input['product_master_id'])?$input['product_master_id']:'0';
        $postData['product_category_id'] = $input['product_category_id'];
        $postData['product_type'] = $input['product_type'];
        $postData['product'] = $input['product'];
         $postData['slug'] = Str::slug($input['product']);
         
        $postData['base_price'] = $input['base_price'];
        $postData['description'] = htmlentities($input['description']);
        $postData['base_uom'] = isset($input['base_uom'])?$input['base_uom']:NULL;
        $postData['tally_name'] = strtoupper(str_replace(' ', '-',isset($input['tally_name'])?$input['tally_name']:$input['product']));
        
        $postData['meta_title'] = strtoupper(str_replace(' ', '-',isset($input['tally_name'])?$input['tally_name']:$input['product']));
        $postData['meta_description'] = strtoupper(str_replace(' ', '-',isset($input['tally_name'])?$input['tally_name']:$input['product']));
        $postData['meta_keyword'] = strtoupper(str_replace(' ', '-',isset($input['tally_name'])?$input['tally_name']:$input['product']));
        $postData['is_active'] = isset($input['is_active']) ? $input['is_active'] : 1;
        $postData['is_pack'] = isset($input['is_pack']) ? $input['is_pack'] : 0;
        $postData['is_sale'] = isset($input['is_sale']) ? $input['is_sale'] : 0;
        $postData['is_new'] = isset($input['is_new']) ? $input['is_new'] : 0;
        $postData['is_gift'] = isset($input['is_gift']) ? $input['is_gift'] : 0;
        $postData['is_featured'] = isset($input['is_featured']) ? $input['is_featured'] : 0;
        $postData['show_on_website'] = isset($input['show_on_website']) ? $input['show_on_website'] : 0;
        $postData['overall_stock_mgmt'] = isset($input['overall_stock_mgmt']) ? $input['overall_stock_mgmt'] : 0;
       
        if(isset($input['product_code']) && !empty($input['product_code'])) {
            $res = 'Updated';
            $postData['modified'] = date('Y-m-d H:i:s');
            $postData['modified_by'] = auth()->user()->id;
            $postData['product_code'] = $input['product_code'];
        }else{
            $res = 'Created';
            $postData['created'] = date('Y-m-d H:i:s');
            $postData['created_by'] = auth()->user()->id;
            $postData['product_code'] = Str::random(12);

        }
        //print_r($postData);exit;
        
    	 $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompanyDir().'files/';
        }
        //return $companyName;
        if($request->hasFile('banner_image')){
            $original_filename = $request->file('banner_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('banner_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('banner_image')->move('./public/upload/'.$companyName.'product/', $image)) {
                
                    $postData['banner_image'] = $image;
                } else {
                    //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    // return $this->sendError('Cannot upload file');
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        
        if($request->hasFile('featured_image')){
           $original_filename = $request->file('featured_image')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('featured_image')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
            
                if ($request->file('featured_image')->move('./public/upload/'.$companyName.'product/', $image)) {
                    // if ($request->file('featured_image')->move('./public/upload/product/', $image)) {
                    $postData['featured_image'] = $image;
                } else {
                    //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    // return $this->sendError('Cannot upload file');
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        
        
        // print_r($postData);exit;
                
        // try {
            $post['product'] = product::updateOrCreate(['product_code'=>$postData['product_code']],$postData);
            
            if($post['product']){
                $productProductCategory['product_id'] = $post['product']->id;
                $productProductCategory['product_category_id'] = $post['product']->product_category_id;
                    //   print_r($product);exit;
                $postProductProductCategory = ProductProductCategory::updateOrCreate([
                    'product_id'=> $productProductCategory['product_id'],'product_category_id'=> $productProductCategory['product_category_id']
                    ],$productProductCategory);
                
                if($request->hasFile('product_images')){
                    
                    $files = $request->file('product_images');
                    foreach($files as $other_images){
                    
                        $postData['other_images'] = [];
                       
                            $res = 'Created';
                            $postData['other_images']['created'] = date('Y-m-d H:i:s');
                    
                            $original_filename = $other_images->getClientOriginalName();
                            $original_filename_arr = explode('.', $original_filename);
                            $file_ext = end($original_filename_arr);
                            $file_type = $other_images->getMimeType();
                            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
                          
                            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                        	
                                if ($other_images->move('./public/upload/'.$companyName.'product/', $image)) {
                                // if ($other_images->move('./public/upload/product/', $image)) {
                                    $postData['other_images']['image_name_1'] = $image;
                                
                                } else {}
                            } else {}
                        $postData['other_images']['type'] = 'image';
                        $postData['other_images']['product_id'] = $post['product']->id;
                        
                        print_r($postData['other_images']); //exit;
                       $post['other_images'][] = ProductImages::create($postData['other_images']);
                        // $post['product_images'][$key] = $temp;
                    }
                    if(isset($post['other_images']) && (count($post['other_images'])>0)){
                         return response()->json([
                            'status' => "success",
                            'message' => "Product ".$res." And Images uploaded Successfully",
                            'data' => $post
                        ],200);
                    } else {
                         return response()->json([
                            'status' => "success",
                            'message' => "Product ".$res." Successfully",
                            'data' => $post
                        ],200);
                    }
                } else {
                    
                    return response()->json([
                        'status' => "success",
                        'message' => "Product ".$res." Successfully",
                        'data' => $post
                    ],200);
                }
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            } try {
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }

    }
    
    public function destroyProduct(Request $request)
    {
        $company = Helper::getCompany();
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (NULL !== $input['is_active'])?0:1;
        
        $postData['modified'] = date('Y-m-d H:i:s');
        $product = DB::table($company.'products')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($product){
            $product = DB::table($company.'products')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg
        ],200);
      
    }
    
    public function createProductCategory(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
            // 'gst' => 'required',
            'hsn_code' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
    
       $input = $request->all();
       
        $postData['is_service'] = $input['is_service'];
        $postData['parent_id'] = isset($input['parent_id']) ? $input['parent_id'] : 0;
        $postData['category_name'] = $input['category_name'];
        $postData['description'] = $input['description'];
        $postData['slug'] = Str::slug($input['category_name']);
        $postData['hsn_code'] = $input['hsn_code'];
        $postData['gst'] = isset($input['gst'])?$input['gst']:'';
        $postData['meta_title'] = $input['category_name'];
        $postData['meta_description'] = $input['description'];
        $postData['meta_keyword'] = $input['category_name'];
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['created_by'] = auth()->user()->id;
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = auth()->user()->id;
       
       
       //imagename_1
        
        if($request->hasFile('image_name_1')){
            $original_filename = $request->file('image_name_1')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('image_name_1')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
            		 $companyName = '';
                    if(Helper::getCompany()){
                        $companyName = Helper::getCompanyDir().'files/';
                    }
                   //return $companyName;  
                if ($request->file('image_name_1')->move('./public/upload/'.$companyName.'product/', $image)) {
                    // if ($request->file('image_name_1')->move('./public/upload/product/', $image)) {
                    $postData['image_name_1'] = $image;
                } else {
                    //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    // return $this->sendError('Cannot upload file');
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        
        /*return response()->json([
                    'status' => 'success',
                    'message' => $postData
                ]);*/
        try {
            $post = ProductCategories::updateOrCreate(['slug'=>$postData['slug']],$postData);

            if($post){
                return response()->json([
                    'status' => 'success',
                    'message' => "Product Category Created Successfully"
                ]);
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
    
    public function productCategoryList(Request $request)
    {
      
      $postData = $request->all();
        //   echo "hii";exit;
          $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
          $company = Helper::getCompany();
        try {
            $query = DB::table($company.'product_categories as pc')->select('pc.*')->where('pc.is_active','=','1');
            
             $searchQuery = '';
             //print_r($stalls->toSql());exit;
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
                   $searchQuery = " (pc.category_name like '%".$searchValue."%' or pc.is_active like '%".$searchValue."%')";
                }
               if(isset($postData['product_category']) && (NULL !== $postData['product_category'])){
                    $query->where('pc.category_name', '=', $postData['product_category']);
                }
                if(isset($postData['is_service']) && (NULL !== $postData['is_service'])){
                    $query->where('pc.is_service', '=', $postData['is_service']);
                }else{
                    $query->where('pc.is_service', '=', '0');
                }
                $sql = $query;
                $productCategoryList = $sql->count();
                $totalRecords = $productCategoryList;
                // echo $totalRecords;exit;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                // echo $sql3->toSql();exit;
                $productCategoryList = $sql3->get();
                //$productCategoryList =  $query->where('pc.is_active', 'LIKE', '1')->orderBy('pc.created', 'desc')->get();

                if($productCategoryList){
                    $companyName = '';
                    if(Helper::getCompany()){
                        $companyName = Helper::getCompanyDir().'files/';
                    }
                    foreach($productCategoryList as $key => $value){
                         $productCategoryList[$key]->sr_no = ($key+1);
                         $productCategoryList[$key]->description = strip_tags($value->description);
                         $productCategoryList[$key]->image_name_1 = URL('/public/upload/'.$companyName.'product/').'/'.$value->image_name_1;
                         $productCategoryList[$key]->image_name_2 = URL('/public/upload/'.$companyName.'product/').'/'.((NULL !== $value->image_name_2)?$value->image_name_2:$value->image_name_1);
                        //  $productCategoryList[$key]->image_name_1 = URL('/public/upload/product/').'/'.$value->image_name_1;
                        //  $productCategoryList[$key]->image_name_2 = URL('/public/upload/product/').'/'.((NULL !== $value->image_name_2)?$value->image_name_2:$value->image_name_1);
                    }
                          $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $productCategoryList
                     );
                    return response()->json(['status'=>'success','data'=>$response]);
                }else{
                    return response()->json(['status'=>'error','message'=>'Something went wrong']);
                }
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getProductCategory(Request $request)
    {
        $company = Helper::getCompany();
           $postData = $request->all();
    //   echo "hii";exit;
     try {
            $query = DB::table($company.'product_categories')->select(['id', 'category_name']);
            if(isset($postData['is_service']) && NULL !== $postData['is_service']){
                $query->where('is_service', '=', $postData['is_service']);
            } else {
                $query->where('is_service', '=', '0');
            }
           $data =  $query->where('is_active', 'LIKE', '1') ->orderBy('created_at', 'desc')->get();

            foreach($data as $key => $value){
                $productCategoryList[$value->id] = $value->category_name;
            }
             if($productCategoryList){
                return response()->json(['status'=>'success','data'=>$productCategoryList]);
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
    
    public function getProductCategoryDetail(Request $request)
    {
        $company = Helper::getCompany();
           $postData = $request->all();
           //echo "hii";exit;
         try {
            $query = DB::table($company.'product_categories')->select('*');
            if(isset($postData['is_service']) && NULL !== $postData['is_service']){
                $query->where('is_service', '=', $postData['is_service']);
            } else {
                $query->where('is_service', '=', '0');
            }
            if(isset($postData['slug']) && NULL !== $postData['slug']){
                $query->where('slug', '=', $postData['slug']);
            } else {
               // $query->where('id', '=', '0');
            }
           $data =  $query->where('is_active', 'LIKE', '1') ->orderBy('created_at', 'desc')->get();
           //return $data;
            $companyName = '';
            if(Helper::getCompany()){
                $companyName = Helper::getCompanyDir().'files/';
            }
            foreach($data as $key => $value){
                $data[$key]->image_name_1 = URL('/public/upload/'.$companyName.'product/').'/'.$value->image_name_1;
                // $data[$key]->image_name_1 = URL('/public/upload/product/').'/'.$value->image_name_1;
            }
             if($data){
                return response()->json(['status'=>'success','message'=>'Product Category Detail', 'data'=>$data]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1], 'db_error'=>$e->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function destroyProductCategory(Request $request)
    {
        $company = Helper::getCompany();
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (NULL !== $input['is_active'])?0:1;
        
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $product = DB::table($company.'product_categories')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($product){
            $product = DB::table($company.'product_categories')->where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg
        ],200);
      
    }
    
    public function createSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_name' => 'required',
            'parameter' => 'required',
            'value' => 'required',
            'datatype' => 'required',
            'priority' => 'required'
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
   
       $input = $request->all();
       
        $postData['module_name'] = $input['module_name'];
        $postData['parameter'] = $input['parameter'];
        $postData['value'] = $input['value'];
        $postData['datatype'] = $input['datatype'];
        $postData['priority'] = $input['priority'];
        $postData['created'] = date('Y-m-d H:i:s');
       
        
        // print_r($postData);exit;
        try {
            $post = setup::updateOrCreate(['parameter'=>$postData['parameter']],$postData);

            if($post){
                return response()->json([
                    'status' =>  "success",
                    'message' => "Setup Successfully"
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
    
    public function setupList(Request $request)
    {
      
    //   echo "hii";exit;
     try {
            $users = setup::select('*')->where('is_active', 'LIKE', '1')->get();

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
    
    
    public function destroySetup(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = (NULL !== $input['is_active'])?0:1;
        
        $postData['modified'] = date('Y-m-d H:i:s');
        $product = setup::where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($product){
            $product = setup::where('id', $input['id'])->first();
            $msg = 'Detail updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg
        ],200);
      
    }
    
    public function getModuleList(Request $request)
    {
      
    //   echo "hii";exit;
     try {
            $users = setup::select('module_name')->where('is_active', 'LIKE', '1')->get();

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
    
    //test api kanak
    public function productlist(Request $request)
    {
        $company = Helper::getCompany();
           //echo "hii";exit;
        $postData = $request->all();
        try {
           $query = DB::table($company.'products')->select($company.'products.*', 'pc.hsn_code','pi.image_name_1','pi.image_name_2')
            ->join($company.'product_categories as pc', 'pc.id', '=', $company.'products.product_category_id')
            ->join($company.'product_images as pi', 'pi.product_id', '=', 'products.id');
            if(isset($postData['product_category_id']) && NULL !== $postData['product_category_id']){
                $query->where('product_category_id', '=', $postData['product_category_id']);
            } 
            
            $product = $query->where($company.'products.is_active', 'LIKE', '1')->get();


             if($product){
                return response()->json(['status'=>'success','data'=>$product]);
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
    
     public function productLists(Request $request)
    {
      
          //echo "hii";exit;
        $postData = $request->all();
        try {
            $query = product::select('*')->where('product_category_id',$postData['product_category_id']);
                $product = $query->ProductImages::select('*')->hasProductImages($postData['product_id'])->get();
           //echo "<pre>";print_r($product);exit;

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getVehiclesMakeModelType(Request $request){
        
        // use App\Models\Vehicles;
        // use App\Models\VehicleType;
        // use App\Models\VehicleManufacturer;
        // use App\Models\VehicleModel;
        // use App\Models\VehicleVariation;
        // use App\Models\VehicleFeature;
        $vehicles = Vehicles::groupBy('vehicle_status')->get();
        foreach($vehicles as $vKey => $vehicle){
            $vehicles[$vKey]->types =  Vehicles::select(['vt.*'])->join('vehicle_types as vt','vt.id', '=', 'vehicles.vehicle_type_id')->groupBy('vehicle_type_id')->where('vehicle_status', 'LIKE', $vehicle->vehicle_status)->get();
        }
         return response()->json(['status'=>'success','data'=>$vehicles]);
        try{
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
        
    }
    
    public function productCategoryList2(Request $request)
    {
      
      $postData = $request->all();
        //   echo "hii";exit;
          $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
          $company = Helper::getCompany();
        try {
            $query = DB::table($company.'product_categories as pc')->select('pc.*')->where('pc.is_active','=','1');
            
             $searchQuery = '';
             //print_r($stalls->toSql());exit;
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
                   $searchQuery = " (pc.category_name like '%".$searchValue."%' or pc.is_active like '%".$searchValue."%')";
                }
               if(isset($postData['product_category']) && (NULL !== $postData['product_category'])){
                    $query->where('pc.category_name', '=', $postData['product_category']);
                }
                if(isset($postData['is_service']) && (NULL !== $postData['is_service'])){
                    $query->where('pc.is_service', '=', $postData['is_service']);
                }else{
                    $query->where('pc.is_service', '=', '0');
                }
                $sql = $query;
                $productCategoryList = $sql->count();
                $totalRecords = $productCategoryList;
                // echo $totalRecords;exit;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                // echo $sql3->toSql();exit;
                $productCategoryList = $sql3->get();
                //$productCategoryList =  $query->where('pc.is_active', 'LIKE', '1')->orderBy('pc.created', 'desc')->get();

                if($productCategoryList){
                    $companyName = '';
                    if(Helper::getCompany()){
                        $companyName = Helper::getCompanyDir().'files/';
                    }
                     foreach($productCategoryList as $key => $value){
                         $productCategoryList[$key]->sr_no = ($key+1);
                         $productCategoryList[$key]->description = strip_tags($value->description);
                         $productCategoryList[$key]->image_name_1 = URL('/public/upload/'.$companyName.'product/').'/'.$value->image_name_1;
                         $productCategoryList[$key]->image_name_2 = URL('/public/upload/'.$companyName.'product/').'/'.((NULL !== $value->image_name_2)?$value->image_name_2:$value->image_name_1);
                        // $productCategoryList[$key]->image_name_1 = URL('/public/upload/product/').'/'.$value->image_name_1;
                        //  $productCategoryList[$key]->image_name_2 = URL('/public/upload/product/').'/'.((NULL !== $value->image_name_2)?$value->image_name_2:$value->image_name_1);
                    }
                          $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $productCategoryList
                     );
                    return response()->json(['status'=>'success','data'=>$response]);
                }else{
                    return response()->json(['status'=>'error','message'=>'Something went wrong']);
                }
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function createBrand(Request $request){
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required',
            'description' => 'required',
            'logo' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);
        $res = '';
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        $input = $request->all();
        
        // print_r($input);exit;
        $postData['brand_name'] = $input['brand_name'];
        
        $postData['description'] = $input['description'];
        
        $postData['is_active'] = isset($input['is_active']) ? $input['is_active'] : 1;
        if(isset($input['id']) && !empty($input['id'])) {
            $res = 'Updated';
            $postData['modified_at'] = date('Y-m-d H:i:s');
            $postData['modified_by'] = auth()->user()->id;
            $postData['id'] = $input['id'];
        }else{
            $res = 'Created';
            $postData['created_at'] = date('Y-m-d H:i:s');
            $postData['created_by'] = auth()->user()->id;
            $postData['id'] = '';

        }
        
        if($request->hasFile('logo')){
            $original_filename = $request->file('logo')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('logo')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
            	$logo = $original_filename_arr[0].time(). '.'.$file_ext;
            		 $companyName = '';
                    if(Helper::getCompanyDir()){
                        $companyName = Helper::getCompanyDir().'files/';
                    }
                    //return $companyName.' '.$logo;
                if ($request->file('logo')->move('./public/upload/'.$companyName.'product/brands/', $logo)) {
                    // if ($request->file('image_name_1')->move('./public/upload/product/', $image)) {
                    $postData['logo'] = $logo;
                } else {
                    //return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
                    // return $this->sendError('Cannot upload file');
                }
            }else{
                // return $this->sendError('Enter Valid File Format');
                //return json_encode(['message'=>'Enter Valid File Format', 'status'=>'fail']);
            }
        }
        
         //print_r($postData);exit;
        try {
            
          //$company = Helper::getCompany();
          //return $company;
            $post = Brands::updateOrCreate(['id'=>$postData['id']],$postData);
           
            if($post){
                return response()->json([
                    'status' =>  "success",
                    'message' => "Brand ".$res." Successfully",
                    'data' => $post
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
    
    
    public function getBrandList(Request $request)
    {
      
        $postData = $request->all();
        //   echo "hii";exit;
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
          $company = Helper::getCompany();
        try {
            $query = Brands::select('*');
            
             $searchQuery = '';
             //print_r($stalls->toSql());exit;
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
                   $searchQuery = " (brand_name like '%".$searchValue."%' or is_active like '%".$searchValue."%')";
                }
                if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
                    $query->where('is_active', '=', $postData['is_active']);
                }else{
                    $query->where('is_active', '=', '1');
                }
                $sql = $query;
                $brandsList = $sql->count();
                $totalRecords = $brandsList;
                // echo $totalRecords;exit;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                 //echo $sql3->toSql();exit;
                $brandsList = $sql3->get();
                
                if($brandsList){
                    $companyName = '';
                    if(Helper::getCompany()){
                        $companyName = Helper::getCompanyDir().'files/';
                    }
                    foreach($brandsList as $key => $value){
                         $brandsList[$key]->sr_no = ($key+1);
                         $brandsList[$key]->id = $value->id;
                         $brandsList[$key]->brand_name = $value->brand_name;
                         $brandsList[$key]->description = strip_tags($value->description);
                         $brandsList[$key]->logo = URL('/public/upload/'.$companyName.'product/brands/').'/'.$value->logo;
                    }
                          $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $brandsList
                     );
                    return response()->json(['status'=>'success','data'=>$response]);
                }else{
                    return response()->json(['status'=>'error','message'=>'Something went wrong']);
                }
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
     public function deleteBrand(Request $request)
    {
        $company = Helper::getCompany();
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $input = $request->all();
        // print_r($input);exit;
        $postData['is_active'] = isset($input['is_active']) ? $input['is_active'] : 0;
        $postData['modified_at'] = date('Y-m-d H:i:s');
        $postData['modified_by'] = auth()->user()->id;
        $brand = DB::table($company.'manufacturing_brands')->where('id', $input['id'])->update($postData);

        $msg = 'Some Error Occurred';
        if($brand){
            $brand = DB::table($company.'manufacturing_brands')->where('id', $input['id'])->first();
            $msg = 'Brand updated successfully';
        }
        
        return response()->json([
            'status' =>  "success",
            'message' => $msg,
            'data' => $brand
        ],200);
      
    }
}
