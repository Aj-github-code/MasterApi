<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

use App\Models\Vehicles;
use App\Helpers\Helper as Helper;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NavigationController extends Controller
{
    
    public $company;
    
    public function getProductCategories($parentId){
         
        $this->company = Helper::getCompany();//session('company_table_name');
        $menu = [];
        $category = DB::table($this->company.'product_categories')->where('parent_id', '=', $parentId)->where('is_active', '=', '1')->get();
        if($category->count()){
       
            foreach($category as $ckey => $cValue){
                $childCategories = $this->getProductCategories($cValue->id);
                if($childCategories['status']){
                    $menu[] =  ['name'=>$cValue->category_name, 'slug'=>$cValue->slug, 'sub_menu'=>$childCategories['data']];
                } else {
                    $menu[] = ['name'=>$cValue->category_name, 'slug'=>$cValue->slug, 'sub_menu'=>[]];  
                }
            }
             return  ['status'=>true, 'data'=>$menu];
        } else {
            return ['status'=>false];
        }
    }
    
    
    public function index(Request $request){
        DB::enableQueryLog();
        $company = Helper::getCompany();
        //echo $company;exit;
        $prefix = DB::table('companies')->select(['tbl_prefix', 'website'])->where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
        /*Log::error("prefix=".json_encode($prefix));
        Log::error(json_encode(DB::getQueryLog()));
        print_r($prefix);exit;*/
        if(NULL!==$prefix){
            $this->company = $prefix->tbl_prefix."_";
        }
        //$this->company = Helper::getCompany();//session('company_table_name');
        $postData = $request->all();
        if(!isset($postData['menu_type'])){
            $postData['menu_type'] = 'Frontend Main Menu';
        }
        //return response()->json(['status'=>'success', 'message'=>'Menus Found', 'data'=>$this->company]);
        
        $menuType = DB::table($company.'menus')->where('name', $postData['menu_type'])->first();
       
        if($menuType){
            $menu = DB::table($company.'navigation')->where('menu_id', $menuType->id)->where('is_active', 1)->orderBy('priority', 'asc')->get();
            $masterMenu = [];
            foreach($menu as $key => $value){
                $NavMenu = [];
                $sub_menu = false;
                $module = json_decode($value->module);
                if($value->has_dynamic_child){
                    $sub_menu = [];
                    // print_r(json_decode($value->module));exit;
                    if($module->module === 'product_categories'){
                        $productCategories = DB::table($company.$module->module)->where('parent_id', '=', '0')->where('is_service', '=', '0')->where('is_active', '=', '1')->get();
                        if($productCategories){
                            foreach($productCategories as $pckey => $pcValue){
                                $childCategories = $this->getProductCategories($pcValue->id);
                                    // print_r($childCategories);exit;
                                if($childCategories['status']){
                                    // return response($childCategories['status']);
                                     $sub_menu[] =['name'=>$pcValue->category_name, 'slug'=>$pcValue->slug, 'sub_menu'=>$childCategories['data']];
                                } else {
                                    $sub_menu[] = ['name'=>$pcValue->category_name, 'slug'=>$pcValue->slug, 'sub_menu'=>[]];  
                                }
                            }
                             $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>$sub_menu];  
                        } else {
                             $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>[]];  
                        }
                    } else if($module->module === 'vehicles'){
                        $vehicles = DB::table($module->module)->where('is_active', '=', '1')->groupBy('vehicle_status')->get();
                        if($vehicles){
                            foreach($vehicles as $vkey => $vValue){
                                $vehicleType =  Vehicles::select(['vt.*'])
                                                            ->join('vehicle_types as vt','vt.id', '=', 'vehicles.vehicle_type_id')
                                                            ->groupBy('vehicle_type_id')
                                                            ->where('vehicle_status', 'LIKE', $vValue->vehicle_status)
                                                            ->get();
                                    // print_r($childCategories);exit;
                                if($vehicleType){
                                    $sub_sub_menu = [];
                                    foreach($vehicleType as $vtKey => $vTValue){
                                        $sub_sub_menu[] = ['name'=>$vTValue->vehicle_type, 'slug'=>$vTValue->vehicle_type, 'sub_menu'=>[]];
                                    }
                                    // return response($childCategories['status']);
                                     $sub_menu[] =['name'=>$vValue->vehicle_status, 'slug'=>Str::slug($vValue->vehicle_status), 'sub_menu'=>$sub_sub_menu];
                                } else {
                                    $sub_menu[] = ['name'=>$vValue->vehicle_status, 'slug'=>Str::slug($vValue->vehicle_status), 'sub_menu'=>[]];  
                                }
                            }
                             $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>$sub_menu];  
                        } else {
                             $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>[]];  
                        }  
                  } else {
                          
                           $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>[]];  
                      }
                } else {
                    
                    $NavMenu[] = ['name'=>$value->name, 'slug'=>$value->slug, 'sub_menu'=>$sub_menu];  
                }
                
                // print_r($value->parent_id);exit;
                $masterMenu[] = array_merge($NavMenu[0], ['id'=>$value->id,'parent_id'=>$value->parent_id]);
               
            }
            
            $multiDimensionalNavigation = $this->childNavigation($masterMenu);
            // print_r( $masterMenu[1]);exit;
            return response()->json(['status'=>'success', 'message'=>'Menus Found', 'data'=>$multiDimensionalNavigation]);
        } else {
            return response()->json(['status'=>'success', 'message'=>'No Such Menus Available']);
        }
    }
    
    
    public function childNavigation($items, $parentId = 0){
        $result = [];

        foreach ($items as $item) {
            if(isset($item['id']) && (NULL !== $item['id'])){
                
                if ($item['parent_id'] == $parentId) {
                    $children = $this->childNavigation($items, $item['id']);
                    if ($children) {
                        $item['sub_menu'] = $children;
                    }
                    $result[] = $item;
                }
            }
        }
    
        return $result;
    }
    
    public function createTempMenu(Request $request){
        
        
    }
    
    public function uploadExcel(Request $request){
       // print_r($request);exit;
        
            $prefix = DB::table('companies')->select(['tbl_prefix', 'website'])->where('website','LIKE',"%".$_SERVER['SERVER_NAME']."%")->where('is_active', true)->first();
            /*Log::error("prefix=".json_encode($prefix));
            Log::error(json_encode(DB::getQueryLog()));
            print_r($prefix);exit;*/
            if(NULL!==$prefix){
                $this->company = $prefix->tbl_prefix."_";
            }
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
                    //print_r($column);
                    //print_r($key);
                    if($key>1) {
                        $table = $this->company.'navigation';
                        //print_r($data[0][$rKey]);
                        if(!empty($data[1][$rKey])){
                            $columns =  strtolower(str_replace("*","",str_replace(" ","_", $data[1][$rKey])));  
                            //echo 'echo'; print_r($columns);
                        }
                        
                        if(!empty($column)){
                            if(!empty($columns)){
                                       $mainData[$count][$table][$columns][$column] = $data[$key][$rKey+1];
                                   
                                
                            }else {
                                  $mainData[$count][$table] = $column;
                            }
                        }
                        
                    }
                }
                $count = $count +1;
                //print_r($mainData);
            }
            foreach($mainData as $key => $table){
                print_r($table);
            }exit;
        
    }
}