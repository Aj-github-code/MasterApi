<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\movies;
use App\Models\crew_members;
use App\Models\movie_images;
use App\Models\awards;
use App\Models\movie_gallery;
use App\Models\movie_links;
use Illuminate\Http\Request;
use Validator;
use DB;


class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        // echo "hii";exit;
        // $post = movies::all();

        // return response()->json([
        //     'status' => true,
        //     'post' => $post
        // ],200);

        $postData = $request->all();


        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        //DB::enableQueryLog();
        $stalls = DB::table('movies')
            ->join('movie_gallery', 'movie_gallery.movie_id', 'LIKE', 'movies.id', 'LEFT')
            // ->where('movie_gallery.is_banner', "1")
            ->select(['movies.*', 'movie_gallery.images'])
            ->where('movies.is_active', "1");

            if(isset($postData['is_banner']) && !empty($postData['is_banner'])) {
                $stalls->where('movie_gallery.is_banner', $postData['is_banner']);
            }
        
        // $stalls = 'Select * from movies where is_active=1';    
        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        // $stalls = movies::all();
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            // echo "helloo";exit;
            $postData = $request->post();
            //echo '<pre>';print_r($postData);exit;
            ## Read value
            $draw = $postData['draw'];
            $start = $postData['start'];
            $rowperpage = $postData['length']; // Rows display per page
            $columnIndex = $postData['order'][0]['column']; // Column index
            $columnName = $postData['columns'][$columnIndex]['data']; // Column name
            $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
            $searchValue = $postData['search']['value']; // Search value
         
            if($searchValue != ''){
               $searchQuery = " (t.name like '%".$searchValue."%' or t.release_date like '%".$searchValue."%')";
            }
        }
        
        // echo $stalls;exit;
        $sql = $stalls;
        //print_r(DB::getQueryLog());exit;
        $records = $sql->count();
        // echo $records;exit;
        // $records = 'Select count(*) from movies';
        //print_r(DB::getQueryLog());exit;
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
        // print_r($sql2->toSql());exit;
        // $sql2 = 'Select count(*) as allcount from ('.$sql.') t where 1=1 '.$searchQuery;
        // echo $sql2;exit;
        // $totalRecordwithFilter = DB::select($sql2);
        
        // $totalRecordwithFilter = $records[0]->allcount;
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
        // $sql2 = 'Select * from ('.$sql.') t where 1=1 '.$searchQuery.' order by '.$columnName.' '.$columnSortOrder;
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
            // $sql2.=' LIMIT '.$start.', '.$rowperpage;
        }
        // $sql3->get();
        // echo $sql3->toSql();exit;
        $records = $sql3->get();
        $data = array();
        foreach($records as $recordKey => $record ){
            // echo '<pre>';print_r($record->name);exit;
           $data[] = array(
            "sr_no" => $recordKey+1,
            "id"=>$record->id,
            "name"=>$record->name,
            "title"=>$record->title,
            "synopsis"=>$record->synopsis,
            "release_date"=>$record->release_date,
            "movie_year"=>$record->movie_year,
            "movie_time"=>$record->movie_time,
            "movie_lang"=>$record->movie_lang,
            "images"=>$record->images,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "slug"=>$record->slug,
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
    public function movieCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "synopsis" => "required",
            "release_date" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        $input = $request->all();

        try {
            $post = movies::create($input);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Movie Created Successfully",
                    'post' => $post
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

    public function awardsCreate(Request $request)
    {
        // echo "hii";exit;
        $validator = Validator::make($request->all(), [
            "award_name" => "required",
            "category" => "required",
            "crew_name" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        // $input = $request->all();
        $movieId = DB::table('movies')->select('id')->where('slug','=',$request->slug)->first();

        // print_r($movieId);exit;
        
        $inputs['award_name'] = $request->award_name;
        $inputs['movie_id'] = $movieId->id; 
        $inputs['slug'] = $request->slug;
        $inputs['category'] = $request->category;
        $inputs['crew_name'] = $request->crew_name;

        try {
            $post = awards::create($inputs);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Awards Created Successfully",
                    'post' => $post
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

    public function castCrewCreate(Request $request)
    {
        // echo "hii";exit;
        $input= new crew_members();
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "role" => "required",
            "image" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        
        if($request->has('image')){
            $image = $request->image;
            $name = time().'.'.$image->getClientOriginalExtension();
            $path = public_path('upload');
            $image->move($path,$name);
        }else{
            $image = NULL;
        }

        $movieId = DB::table('movies')->select('id')->where('slug','=',$request->slug)->first();

        // print_r($movieId);exit;
        
        $inputs['name'] = $request->name;
        $inputs['movie_id'] = $movieId->id; 
        $inputs['slug'] = $request->slug;
        $inputs['image'] = $name;
        $inputs['role'] = $request->role;
        // print_r($inputs);exit;
        try {
            $post = crew_members::create($inputs);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Crew Member Created Successfully",
                    'post' => $post
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

    public function movieGalleryCreate(Request $request)
    {
        // echo "hii";exit;
        $input= new movie_gallery();
        $validator = Validator::make($request->all(), [
            "images" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        $movieId = DB::table('movies')->select('id')->where('slug','=',$request->slug)->first();
        // print_r($movieId);exit;
        $images = $request->file('images');
        $insertData = [];
        foreach($images as $key => $image){
            $name = rand().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('upload'),$name);
            $insertData[$key]['images'] = $name;
            $insertData[$key]['movie_id'] = $movieId->id; 
            $insertData[$key]['slug'] = $request->slug; 
        }

        // print_r($insertData);exit;

        try {
            $post = movie_gallery::insert($insertData);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Images Uploaded Successfully",
                    'post' => $post
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
    public function movieLinksCreate(Request $request)
    {
        // echo "hii";exit;
        $input= new movie_links();
        $validator = Validator::make($request->all(), [
            "link" => "required"
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }

        $movieId = DB::table('movies')->select('id')->where('slug','=',$request->slug)->first();
        // print_r($movieId);exit;
        $insertData = [];

        $insertData['movie_id'] = $movieId->id; 
        $insertData['slug'] = $request->slug; 
        $insertData['link'] = $request->link;
        
       
        // print_r($insertData);exit;

        try {
            $post = movie_links::insert($insertData);

            if($post){
                return response()->json([
                    'status' => true,
                    'message' => "Links Uploaded Successfully",
                    'post' => $post
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


    public function movieGalleryList(Request $request, $slug)
    {
        // echo "hii";exit;
        // print_r($slug);exit;
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $stalls = DB::table('movie_gallery')
            ->where('movie_gallery.slug', $slug)
            ->select(['movie_gallery.*'])
            ->where('movie_gallery.is_active', "1");

        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
         // $searchValue = $postData['search']['value']; // Search value
         
            // if($searchValue != ''){
            //    $searchQuery = " (t.name like '%".$searchValue."%' or t.release_date like '%".$searchValue."%')";
            // }
        }
        
        $sql = $stalls;
        $records = $sql->count();
        // echo $records;exit;
        $totalRecords = $records;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
      
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
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
            "movie_id"=>$record->movie_id,
            "images"=>$record->images,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "slug"=>$record->slug,
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

    public function movieAwardsList(Request $request, $slug)
    {
        // echo "hii";exit;
        // print_r($slug);exit;
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $stalls = DB::table('awards')
            ->where('awards.slug', $slug)
            ->select(['awards.*'])
            ->where('awards.is_active', "1");

        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ? $postData['search']['value'] : ''); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (t.award_name like '%".$searchValue."%' or t.crew_name like '%".$searchValue."%')";
            }
        }
        
        $sql = $stalls;
        $records = $sql->count();
        // echo $records;exit;
        $totalRecords = $records;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
      
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
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
            "movie_id"=>$record->movie_id,
            "award_name"=>$record->award_name,
            "category"=>$record->category,
            "crew_name"=>$record->crew_name,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "slug"=>$record->slug,
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

    public function movieCrewList(Request $request, $slug)
    {
        // echo "hii";exit;
        // print_r($slug);exit;
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $stalls = DB::table('movie_crews')
            ->join('crew_members','crew_members.id','=', 'movie_crews.crew_id', 'LEFT')
            ->join('movies','movies.id', 'movie_crews.movie_id', 'LEFT')
            // ->where('crew_members.id', 'movie_crew.crew_id')
            ->where('movie_crews.slug','LIKE', $slug)
            ->select(['movie_crews.*','crew_members.name','crew_members.role','crew_members.image','movies.directed_by','movies.produced_by','movies.written_by','movies.name'])
            ->where('movie_crews.is_active', "1");

        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            ## Read value
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ? $postData['search']['value'] : ''); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (t.award_name like '%".$searchValue."%' or t.crew_name like '%".$searchValue."%')";
            }
        }
        
        $sql = $stalls;
        $records = $sql->count();
        // echo $records;exit;
        $totalRecords = $records;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
      
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
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
            "movie_id"=>$record->movie_id,
            "name"=>$record->name,
            "role"=>$record->role,
            "image"=>$record->image,
            "directed_by"=>$record->directed_by,
            "produced_by"=>$record->produced_by,
            "written_by"=>$record->written_by,
            "name"=>$record->name,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "slug"=>$record->slug,
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

    public function movieSingleList(Request $request, $slug)
    {
        // echo "hii";exit;
        // print_r($slug);exit;
        $postData = $request->all();

        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        $stalls = DB::table('movies')
            ->join('movie_gallery', 'movie_gallery.movie_id', 'LIKE', 'movies.id', 'LEFT')
            // ->where('movie_gallery.is_banner', "1")
            ->select(['movies.*', 'movie_gallery.images'])
            ->where('movies.slug','LIKE', $slug)
            ->where('movie_gallery.is_banner', "1")
            ->where('movies.is_active', "1");

        $searchQuery = '';
        // print_r($stalls->toSql());exit;
        $searchQuery = ' 1 = 1';
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $postData = $request->post();
            ## Read value
            
            $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
            $start = (isset($postData['start']) ? $postData['start'] : '0');
            $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
            $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
            $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
            $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "desc"); // asc or desc
            $searchValue = (isset($postData['search']['value']) ? $postData['search']['value'] : ''); // Search value
         
            if($searchValue != ''){
               $searchQuery = " (t.name like '%".$searchValue."%' or t.synopsis like '%".$searchValue."%')";
            }
        }
        
        $sql = $stalls;
        $records = $sql->count();
        // echo $records;exit;
        $totalRecords = $records;
        
        $totalRecordwithFilter = $stalls->whereRaw($searchQuery)->count();
      
        $sql3 = $stalls->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
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
            "title"=>$record->title,
            "synopsis"=>$record->synopsis,
            "release_date"=>$record->release_date,
            "images"=>$record->images,
            "is_active"=>$record->is_active,
            "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
            "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
            "slug"=>$record->slug,
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

    public function edit(movies $movies)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\movies  $movies
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, movies $movies)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\movies  $movies
     * @return \Illuminate\Http\Response
     */
    public function destroy(movies $movies)
    {
        //
    }
}
