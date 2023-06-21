<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\claim;
use App\Models\UserEstimation;
use App\Models\ClaimInspectionImage;
use App\Models\ClaimAccidentImage;
use App\Models\Assessment;
use App\Models\product;
use App\Models\AssessmentDetail;

use App\Models\Vehicles;
use App\Models\VehicleType;
use App\Models\VehicleManufacturer;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Models\VehicleVariation;
use App\Models\VehicleFeature;
use App\Models\vehicleImage;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Models\ProductCategories;

use App\Helpers\Helper as Helper;



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
class VehicleController extends Controller
{
    
    public function createVehicleType(Request $request){
          $validator = Validator::make($request->all(), [
            'vehicle_type' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors(), 'errorcode'=>409]);
        }
        
        $postData = $request->all();
        
        $insert['vehicle_type'] = $postData['vehicle_type'];
        $insert['slug'] = Str::slug($postData['vehicle_type']);
        $insert['created_at'] = date('Y-m-d H:i:s');
        $insert['created_by'] = auth()->user()->id;
        
        $insertVehicleType = VehicleType::create($insert);
        
    
        try{
             
            if($insertVehicleType){
                return response()->json(['status'=>'success','message'=> 'Vehicle Type Inserted Successfully','data'=>$insertVehicleType]);
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
    
    public function getVehicleType(Request $request){
        try {
            

            //print_r($searchPost);exit;
            $postData = $request->post();
            $countryId = '';
            
            $query = VehicleType::select(['id','vehicle_type'])->where('is_active', '1');
            
            if(isset($postData['type']) && (NULL !== $postData['type'])){
                $query->where('vehicle_type', 'LIKE', $request->post('type'));
            } else if(isset($postData['id']) && (NULL !== $postData['id'])) {
                $query->where('id', 'LIKE', $request->post('id'));
            }
            
            $vehicleType = $query->get();
            if($vehicleType){
                return response()->json(['status'=>'success','data'=>$vehicleType]);
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
    
    
    public function getVehicleMake(Request $request){
        try {

            $postData = $request->post();
            
            $query = VehicleManufacturer::where('is_active', '1');
            
            if(isset($postData['name']) && (NULL !== $postData['name'])){
                $query->where('name', 'LIKE', $request->post('name'));
            } else if(isset($postData['id']) && (NULL !== $postData['id'])) {
                $query->where('id', '=', $request->post('id'));
            }
            
            $vehicleMake = $query->get();
            if($vehicleMake){
                return response()->json(['status'=>'success','data'=>$vehicleMake]);
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
    
    public function getVehicleModel(Request $request){
        try {

            $postData = $request->post();
            
            $query = VehicleModel::where('is_active', '1');
            
            if(isset($postData['make_id']) && (NULL !== $postData['make_id'])){
                $query->where('make_id', 'LIKE', $request->post('make_id'));
            } else if(isset($postData['vehicle_type_id']) && (NULL !== $postData['vehicle_type_id'])) {
                $query->where('vehicle_type_id', '=', $request->post('vehicle_type_id'));
            } else if(isset($postData['model_code']) && (NULL !== $postData['model_code'])) {
                $query->where('model_code', '=', $request->post('model_code'));
            }
            
            $vehicleModel = $query->get();
            if($vehicleModel){
                return response()->json(['status'=>'success', 'data'=>$vehicleModel]);
            }else{
                return response()->json(['status'=>'error', 'message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getVehicleVariant(Request $request){
        try {

            $postData = $request->post();
            
            $query = VehicleVariant::where('is_active', '1');
            
            if(isset($postData['variant_code']) && (NULL !== $postData['variant_code'])){
                $query->where('variant_code', 'LIKE', $request->post('variant_code'));
            } else if(isset($postData['model_code']) && (NULL !== $postData['model_code'])) {
                $query->where('model_code', 'LIKE', $request->post('model_code'));
            } else if(isset($postData['id']) && (NULL !== $postData['id'])) {
                $query->where('id', '=', $request->post('id'));
            } 
            
            $vehicleVariant = $query->get();
            if($vehicleVariant){
                return response()->json(['status'=>'success', 'data'=>$vehicleVariant]);
            }else{
                return response()->json(['status'=>'error', 'message'=>'Something went wrong']);
            }

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function exportVehicleUpload(Request $request){
        
        $postData = $request->all();
        
        $filename = "Excel Upload.xlsx";
        if(NULL !== $request->post('vehicle_type')){
            $specificationData = VehicleVariation::select(['name', 'value'])->where('vehicle_type_id', $request->post('vehicle_type'))->where('is_active', 1)->get();
            $featureData = VehicleFeature::select(['type', 'value'])->where('vehicle_type_id', $request->post('vehicle_type'))->where('is_active', 1)->get();
            
            $feature = [];
            $specification = [];
            if($specificationData){
                foreach($specificationData as $skey => $sValue){
                    $specification[$sValue->name][] = $sValue->value;
                }
            }
            if($featureData){
                foreach($featureData as $skey => $fValue){
                    $feature[$fValue->type][] = $fValue->value;
                }
            }
            
            if($request->post('vehicle_type') == 1){
                 $filename = 'Four Wheeler '. $filename;
            } else {
                $filename = 'Two Wheeler '. $filename;
            }
            if(NULL !== $request->post('vehicle_status')){
                if($request->post('vehicle_status') === 'used'){
                    $filename = 'Used '.$filename;
                } else {
                     $filename = 'New '.$filename;
                }
                
            }
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            
            $usedVehicles  = ['History', 'Km Driven', 'Last Serviced On', 'Registration No', 'Registered In', 'Owner', 'Insurance Validity'];
            $columns = ['Type', 'System Code', 'Make', 'Model', 'TransMission', 'Fuel Type', 'New/Used'];
            
               $column = 0;
                $sheet->setCellValueByColumnAndRow(1, 1, 'Remarks');
                
                $tableFrom = $sheet->getCellByColumnAndRow($column+1, 3)->getCoordinate();
                $tableFrom1 = $sheet->getCellByColumnAndRow($column+1, 4)->getCoordinate();
               foreach($columns as $field){
                    // $excel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);//->getStyle( $column )->getFont()->setBold( true );;
                    $sheet->setCellValueByColumnAndRow(++$column, 2, $field);
                    // $column++;
                }
                 $tableto = $sheet->getCellByColumnAndRow($column, 3)->getCoordinate();
                   $spreadsheet->getActiveSheet()->mergeCells($tableFrom.':'.$tableto);
                
            if(NULL !== $request->post('vehicle_status')){
                if($request->post('vehicle_status') === 'used'){
                    
                    $sheet->setCellValueByColumnAndRow(++$column, 2, 'For Used Vehicle');
                    $tableFrom = $sheet->getCellByColumnAndRow($column, 2)->getCoordinate();
                    foreach($usedVehicles as $field){
                        // $excel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);//->getStyle( $column )->getFont()->setBold( true );;
                        $sheet->setCellValueByColumnAndRow($column, 3, $field);
                        ++$column;
                        // $column++;
                    }
                   $tableto = $sheet->getCellByColumnAndRow($column-1, 2)->getCoordinate();
                   $spreadsheet->getActiveSheet()->mergeCells($tableFrom.':'.$tableto);
                }
            }
              $tableto1 = $sheet->getCellByColumnAndRow($column-1, 4)->getCoordinate();
              $spreadsheet->getActiveSheet()->mergeCells($tableFrom1.':'.$tableto1);
            if(count($specification)>0){
                
                $sheet->setCellValueByColumnAndRow($column, 2, 'Specification');
                
                $tableFrom = $sheet->getCellByColumnAndRow($column, 2)->getCoordinate();
                
                foreach($specification as $spKey =>  $field){
                    // $excel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);//->getStyle( $column )->getFont()->setBold( true );;
                    $specColumns = $column;
                    $columnFrom = $sheet->getCellByColumnAndRow($column, 3)->getCoordinate();
                    foreach($field as $value){
                        $sheet->setCellValueByColumnAndRow($column, 4, $value);    
                        ++$column;
                    }
                    $sheet->setCellValueByColumnAndRow($specColumns, 3, $spKey);
                    $columnTo = $sheet->getCellByColumnAndRow($column-1, 3)->getCoordinate();
                    $spreadsheet->getActiveSheet()->mergeCells($columnFrom.':'.$columnTo);
                    // $column++;
                }
                $tableto = $sheet->getCellByColumnAndRow($column-1, 2)->getCoordinate();
                $spreadsheet->getActiveSheet()->mergeCells($tableFrom.':'.$tableto);
            }
            
            if(count($feature)>0){
                
                $sheet->setCellValueByColumnAndRow($column, 2, 'Features');
                
                $tableFrom = $sheet->getCellByColumnAndRow($column, 2)->getCoordinate();
                foreach($feature as $feKey =>  $field){
                    $feaColumns = $column;
                    // $excel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);//->getStyle( $column )->getFont()->setBold( true );;
                    $feaDetailColumn = $feaColumns;
                     $columnFrom = $sheet->getCellByColumnAndRow($column, 3)->getCoordinate();
                    foreach($field as $value){
                         
                         
                        $sheet->setCellValueByColumnAndRow($column, 4, $value);   
                        ++$column;
                    }
                    $sheet->setCellValueByColumnAndRow($feaColumns, 3, $feKey);
                    // $column++;
                    $columnTo = $sheet->getCellByColumnAndRow($column-1, 3)->getCoordinate();
                    $spreadsheet->getActiveSheet()->mergeCells($columnFrom.':'.$columnTo);
                }
                
                 $tableto = $sheet->getCellByColumnAndRow($column-1, 2)->getCoordinate();
                 $spreadsheet->getActiveSheet()->mergeCells($tableFrom.':'.$tableto);
            }
             
                        //   return response()->json(['specification'=>$specification,'feature'=>$feature]);
       
            
            
          
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
          if (ob_get_contents()) ob_end_clean();
        
            $writer->save(base_path($filename));
            // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
            
            $headers = [
                  'Content-Type' => 'application/xlsx',
               ];

            return response()->json(['status'=>'success','message'=>(URL('/').'/'.$filename)]); 
  
        }
    }
    
    
    public function getVehicles(Request $request){
        
            $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicles')->select(['vehicles.*', 'vm.name as vehicle_model', 'vms.name as vehicle_make', 'vt.vehicle_type', 'vt.slug as vehicle_type_slug'])
                   ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
                   ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')
                   ->join('vehicle_types as vt', 'vt.id', '=', 'vehicles.vehicle_type_id')
                   ->where('vehicles.is_active', '=', 1);
                   
                   
            $searchQuery = '';
            //print_r($query->toSql());exit;
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
                   $searchQuery = " (t.fuel_type like '%".$searchValue."%' or t.vehicle_status like '%".$searchValue."%')";
                }
                if(isset($postData['vehile_type']) && NULL !== $postData['vehile_type']){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehile_type']);
                } 
                // if(isset($postData['is_service']) && (NULL !== $postData['is_service'])){
                //     $query->where('pc.is_service', '=', $postData['is_service']);
                // } else {
                //     $query->where('pc.is_service', '=', '0');
                // } 
                if(isset($postData['vehicle_status']) && (NULL !== $postData['vehicle_status'])){
                    $query->where('vehicles.vehicle_status', 'LIKE', $postData['vehicle_status']);
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
          
             if($records){
                $companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }
                 foreach($records as $key => $value){
                     $slug =  Str::slug($value->vehicle_make." ".$value->vehicle_model);
                    if(NULL !== $value->images){
                        // $records[$key]->images = URL('/public/upload/vehicle/').'/'.$value->images;
                        $records[$key]->images = URL('/public/upload/vehicle/images').'/'.$value->images;
                    }
                    $records[$key]->sr_no = $key+1;
                    $records[$key]->slug = $slug;
                    $records[$key]->slugs = strtoupper(str_replace('-', ' ', $slug));
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
        try {

        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    
    }
    
    public function getVehicleByModel(Request $request, $slug){
          try {
              $slug = strtoupper(str_replace('-', ' ', $slug));
              DB::enableQueryLog();
              /*$vehicle = DB::table('vehicles')->select(['vehicles.*', 'vm.name as vehicle_model', 'vms.name as vehicle_make', 'vi.images as document_image', 'vi.type as vehicle_image_type'])
                       ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
                       ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')
                       ->join('vehicle_images as vi', 'vi.vehicle_id', '=', 'vehicles.id')
                       ->orwhereRaw("CONCAT(vms.name, ' ', vm.name) LIKE ?",['%'.$slug.'%'])->first();*/
                $vehicle = DB::table('vehicles')->select(['vehicles.*', 'vm.name as vehicle_model', 'vms.name as vehicle_make'])
                       ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
                       ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')
                       ->orwhereRaw("CONCAT(vms.name, ' ', vm.name) LIKE ?",['%'.$slug.'%'])->first();
                   //return DB::getQueryLog();
                   //return $vehicle;
            if($vehicle){
                $companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }
                if(NULL !== $vehicle->images){
                    $vehicle->images = URL('/public/upload/vehicle/images').'/'.$vehicle->images;
                }
                if(NULL !== $vehicle->featured_image){
                    $vehicle->featured_image = URL('/public/upload/vehicle/images').'/'.$vehicle->featured_image;
                }
                
                       
                $vehicleImages = [];
                $vImages = [];
                $vDocument = [];
                $vehicleImages = vehicleImage::where('vehicle_id', '=',$vehicle->id)->where('is_active','=',1)->orderBy('id', 'DESC')->get();
                if($vehicleImages){
                    foreach($vehicleImages as $vkey => $vImage){
                        if($vImage->type == 'image'){
                            $vImages[] =  URL('/public/upload/vehicle/images').'/'.$vImage->images;
                        } else if($vImage->type === 'document'){
                            $vDocument[] =  URL('/public/upload/vehicle/document').'/'.$vImage->images;
                        }
                    }
                }
                // return response()->json($vImages);
                $vehicle->other_images = $vImages;
                $vehicle->document = $vDocument;
                
                if(NULL !== $vehicle->used_vehicle){
                    
                     $vehicle->used_vehicle = json_decode( $vehicle->used_vehicle);
                     //return $vehicle->used_vehicle;
                }
                
                if(NULL !== $vehicle->specification){
                   
                    $vehicle->specification = json_decode( $vehicle->specification);
                }
                 //return $vehicle->used_vehicle;
                if(NULL !== $vehicle->features){
                      $vehicle->features = json_decode( $vehicle->features);
                }
                return response()->json(['status'=>'success','data'=>$vehicle]);
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
    
    public function importVehicleData(Request $request){
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
            
            $tables = [];
            $columns = [];
            $subColumns = [];
            $subSubColumns= [];
            $mainData = [];
            $count = 0;
            
                foreach($data as $key => $row){
                
                foreach($row as $rKey => $column){
                    if($key>3) {
                        

                        if(!empty($data[1][$rKey])){
                          
                            $columns = strtolower(str_replace("*","",str_replace(" ","_", $data[1][$rKey]))); 
                        }
                        if(!empty($data[2][$rKey])){
                            // $subColumns =  strtolower(str_replace("*","",str_replace(" ","_", $data[2][$rKey])));  
                            $subColumns = $data[2][$rKey];
                        } else {
                            if($rKey <= 6){
                                
                                $subColumns = '';
                            }
                            
                        }
                        
                        if(!empty($data[3][$rKey])){
                            // $subSubColumns = strtolower(str_replace("*","",str_replace(" ","_", $data[3][$rKey]))); 
                            $subSubColumns = $data[3][$rKey];
                        } else {
                            
                            $subSubColumns = '';
                            
                        }
                       
                        if(!empty($column)){
                            if(!empty($columns)){
                                if(!empty($subColumns)){
                                    if(!empty($subSubColumns)){
                                   
                        
                                        $mainData[$count][$columns][$subColumns][$subSubColumns] = $column;
                 
                                    } else { 
                                        if($columns === 'for_used_vehicle'){
                                            $columns = 'used_vehicle';
                                        }
                                        $mainData[$count][$columns][$subColumns] = $column;
                          
                                    }
                                } else {
                              
                                    if($columns === 'new/used')
                                    {
                                        $columns = 'vehicle_status';
                                    }
                                        $mainData[$count][$columns] = $column;
                                }
                            } 
                        } 
                    }
                  
                }
                    $count = $count +1;
            }
            //   return response()->json(['status'=>'success','message'=>'Files Uploaded', 'data'=>$mainData]);
            
               $response = [];
            foreach($mainData as $key => $rows){
            
                if(isset($rows['system_code']) && (NULL !== $rows['system_code'])){
                     $postData['system_code'] = $rows['system_code'];
                } else {
                    $postData['system_code'] = $this->generateSystemCode(isset($rows['make'])?$rows['make']:'TVS');
                }
                if(isset($rows['used_vehicle']) && (NULL !== $rows['used_vehicle'])){
                    $postData['used_vehicle'] = json_encode($rows['used_vehicle']);
                }
                if(isset($rows['specification']) && (NULL !== $rows['specification'])){
                    $postData['specification'] = json_encode($rows['specification']);
                }
                if(isset($rows['features']) && (NULL !== $rows['features'])){
                    $postData['features'] = json_encode($rows['features']);
                }
                if(isset($rows['images']) && (NULL !== $rows['images'])){
                    $postData['images'] =  'tvsbike.png';
                }
                
                $type = isset($rows['type']) ? $rows['type'] : 'N/A';
                $make = isset($rows['make']) ? $rows['make'] : 'N/A';
                $model = isset($rows['model']) ? strtoupper(str_replace('-',' ',( Str::slug($rows['model'], '-')))) : 'N/A';
                $postData['base_price'] = isset($rows['base_price']) ? $rows['base_price'] : '0';
                $postData['transmission'] = isset($rows['transmission']) ? $rows['transmission'] : 'N/A';
                $postData['fuel_type'] = isset($rows['fuel_type']) ? $rows['fuel_type'] : 'N/A';
                $postData['vehicle_status'] = isset($rows['vehicle_status']) ? $rows['vehicle_status'] : 'N/A';
                //return $model;
                $vehicleMakeID = 'N/A';
                
                $vehicleTypeID = 'N/A';
                $vehicleModelID = 'N/A';
                if($type !== 'N/A'){
                    
                    $vehicleType = VehicleType::where('vehicle_type', $type)->first();
                    
                    if(!$vehicleType){
                        $vehicleTypeData['vehicle_type'] = $type;
                        $vehicleTypeData['slug'] =  Str::slug($type, '-');
                        $vehicleTypeData['is_active'] = 1;
                        $vehicleTypeData['created_at'] =  date('Y-m-d H:i:s');
                        $vehicleTypeData['updated_at'] =  date('Y-m-d H:i:s');
                        $vehicleTypeData['created_by'] = auth()->id();
                        $vehicleTypeData['updated_by'] = auth()->id();
                        $vehicleTypeResponse = VehicleType::create($vehicleTypeData);
                        $vehicleTypeID = $vehicleTypeResponse->id;
                    }else{
                        $vehicleTypeID = $vehicleType->id;
                    }
                }
                if($make !== 'N/A'){
                    
                    $vehicleMake = VehicleManufacturer::where('name', $make)->first();
                    if(!$vehicleMake){
                        $vehicleData['name'] = $make;
                        $vehicleData['slug'] =  Str::slug($make, '-');
                        $vehicleData['is_active'] = 1;
                        $vehicleData['created_at'] =  date('Y-m-d H:i:s');
                        $vehicleData['updated_at'] =  date('Y-m-d H:i:s');
                        $vehicleData['created_by'] = auth()->id();
                        $vehicleData['updated_by'] = auth()->id();
                        $vehicleResponse = VehicleManufacturer::create($vehicleData);
                        $vehicleMakeID = $vehicleResponse->id;
                    }else{
                        $vehicleMakeID = $vehicleMake->id;
                    }
                }
                
                if($model !== 'N/A'){
                    
                    $vehicleModel = VehicleModel::where('name', $model)->first();
                    if(!$vehicleModel){
                         $vehicleModelCount = VehicleModel::max('model_code');
                        $vehicleModelData['name'] = $model;
                        $vehicleModelData['slug'] =  Str::slug($model, ' ');
                        $vehicleModelData['is_active'] = 1;
                        $vehicleModelData['created_at'] = date('Y-m-d H:i:s');
                        $vehicleModelData['updated_at'] = date('Y-m-d H:i:s');
                        $vehicleModelData['created_by'] = auth()->id();
                        $vehicleModelData['updated_by'] = auth()->id();
                        $vehicleModelResponse = VehicleModel::create($vehicleModelData);
                        $vehicleModelID = $vehicleModelResponse->id;
                    }else{
                        $vehicleModelID = $vehicleModel->id;
                    
                    }
                }
           
                $postData['vehicle_type_id'] = $vehicleTypeID;
                $postData['make_id'] = $vehicleMakeID;
                $postData['model_id'] =  $vehicleModelID;
                
                try{
                    $res = Vehicles::updateOrCreate(['system_code'=>$postData['system_code']], $postData);
                    if($res){
                        $response[] = ['status'=>'success', 'message'=>'Model No: '.$model.' Entry Successfull', 'data'=>$res];
                    } else {
                        $response[] = ['status'=>'error', 'message'=>'Something Wrong With Model No: '.$model, 'data'=>$res];
                    }
            } catch(\Illuminate\Database\QueryException  $e) {
                $response[] = ['status'=>'error', 'message'=>'Something Wrong With Model No: '.$model, 'data'=>$res];
            } catch(Exception $ex) {
               $response[] = ['status'=>'error', 'message'=>'Something Wrong With Model No: '.$model, 'data'=>$res];
            }  
                
      

            }
             
            if($response){
                return response()->json(['status'=>'success','message'=>'Excel Import Completed', 'data'=>$response]);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

           
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }   
    }
    
    function generateSystemCode($type = 'TVS') {
        do {
            $code = Helper::generateRandomString(6);
        } while (DB::table('vehicles')->where("system_code", "LIKE", $type.'-'.$code)->first());

        return $type.'-'.$code;
    }
    
    public function createVehicle(Request $request){
        
        $returnPostData = $postData = $request->post();
        $input = array();
        //echo "<pre>"; print_r($request->file('images'));exit;
            if(isset($postData['system_code']) && (NULL !== $postData['system_code'])){
                 $input['system_code'] = $postData['system_code'];
            } else {
                $input['system_code'] = $this->generateSystemCode(isset($postData['make'])?$postData['make']:'TVS');
            }
            if(isset($postData['used_vehicle']) && (NULL !== $postData['used_vehicle'])){
                $input['used_vehicle'] = json_encode($postData['used_vehicle']);
            }
            if(isset($postData['specification']) && (NULL !== $postData['specification'])){
                $input['specification'] = json_encode($postData['specification']);
            }
            if(isset($postData['features']) && (NULL !== $postData['features'])){
                $input['features'] = json_encode($postData['features']);
            }
            if(isset($postData['images']) && (NULL !== $postData['images'])){
                $input['images'] =  'tvsbike.png';
            }
            $type = isset($postData['type']) ? $postData['type'] : 'N/A';
            $make = isset($postData['make']) ? $postData['make'] : 'N/A';
            $model = isset($postData['model']) ? $postData['model'] : 'N/A';
            $input['transmission'] = isset($postData['transmission']) ? $postData['transmission'] : 'N/A';
            $input['fuel_type'] = isset($postData['fuel_type']) ? $postData['fuel_type'] : 'N/A';
            $input['vehicle_status'] = isset($postData['vehicle_status']) ? $postData['vehicle_status'] : 'N/A';
            $vehicleMakeID = 'N/A';
            $vehicleTypeID = 'N/A';
            $vehicleModelID = 'N/A';
            if($type !== 'N/A'){
                
                $vehicleType = VehicleType::where('vehicle_type', $type)->first();
                
                if(!$vehicleType){
                    $vehicleTypeData['vehicle_type'] = $type;
                    $vehicleTypeData['slug'] =  Str::slug($type, '-');
                    $vehicleTypeData['is_active'] = 1;
                    $vehicleTypeData['created_at'] =  date('Y-m-d H:i:s');
                    $vehicleTypeData['updated_at'] =  date('Y-m-d H:i:s');
                    $vehicleTypeData['created_by'] = auth()->id();
                    $vehicleTypeData['updated_by'] = auth()->id();
                    $vehicleTypeResponse = VehicleType::create($vehicleTypeData);
                    $vehicleTypeID = $vehicleTypeResponse->id;
                }else{
                    $vehicleTypeID = $vehicleType->id;
                }
            }
            if($make !== 'N/A'){
                
                $vehicleMake = VehicleManufacturer::where('name', $make)->first();
                if(!$vehicleMake){
                    $vehicleData['name'] = $make;
                    $vehicleData['slug'] =  Str::slug($make, '-');
                    $vehicleData['is_active'] = 1;
                    $vehicleData['created_at'] =  date('Y-m-d H:i:s');
                    $vehicleData['updated_at'] =  date('Y-m-d H:i:s');
                    $vehicleData['created_by'] = auth()->id();
                    $vehicleData['updated_by'] = auth()->id();
                    $vehicleResponse = VehicleManufacturer::create($vehicleData);
                    $vehicleMakeID = $vehicleResponse->id;
                }else{
                    $vehicleMakeID = $vehicleMake->id;
                }
            }
            
            if($model !== 'N/A'){
                
                $vehicleModel = VehicleModel::where('name', $model)->first();
                if(!$vehicleModel){
                     $vehicleModelCount = VehicleModel::max('model_code');
                    //$vehicleModelData['make_id'] = $vehicleMakeID;
                    //$vehicleModelData['vehicle_type_id'] = $vehicleTypeID;
                    //$vehicleModelData['model_code'] = isset($postData['model_code']) ? $postData['model_code'] : 'N/A';
                    $vehicleModelData['oem'] = isset($postData['oem']) ? $postData['oem'] : 'N/A';
                    $vehicleModelData['oem_sub_type'] = isset($postData['oem_sub_type']) ? $postData['oem_sub_type'] : 'N/A';
                    $vehicleModelData['is_three_wheeler'] = isset($postData['is_three_wheeler']) ? $postData['is_three_wheeler'] : '0';
                    $vehicleModelData['name'] = $model;
                    $vehicleModelData['slug'] =  Str::slug($model);
                    $vehicleModelData['is_active'] = 1;
                    $vehicleModelData['created_at'] = date('Y-m-d H:i:s');
                    $vehicleModelData['updated_at'] = date('Y-m-d H:i:s');
                    $vehicleModelData['created_by'] = auth()->id();
                    $vehicleModelData['updated_by'] = auth()->id();
                    $vehicleModelResponse = VehicleModel::create($vehicleModelData);
                    $vehicleModelID = $vehicleModelResponse->id;
                    //$vehicleModelCode = $vehicleModelResponse->model_code;
                }else{
                    $vehicleModelID = $vehicleModel->id;
                    //$vehicleModelID = $vehicleModel->model_code;
                }
            }
       
            $input['vehicle_type_id'] = $vehicleTypeID;
            $input['make_id'] = $vehicleMakeID;
            $input['model_id'] =  $vehicleModelID;
            if(isset($postData['description']) && (NULL !== $postData['description'])){
                $input['description'] =  $postData['description'];
            }
            if(isset($postData['base_price']) && (NULL !== $postData['base_price'])){
                $input['base_price'] =  $postData['base_price'];
            }
            //upload Image start
        if($request->hasFile('images')){
            // $validator = Validator::make($request->all(), [
            //     'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // ]);
            $companyName = '';
            if(Helper::getCompany()){
                $companyName = Helper::getCompany().'files/';
            }
            $original_filename = $request->file('images')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('images')->getMimeType();
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$image = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('images')->move('./public/upload/vehicle/images/', $image)) {
                // if ($request->file('images')->move('./public/upload/vehicle/', $image)) {
                    $postData['images'] = $image;
                } else {
                    return json_encode(['message'=>'cannot upload file', 'status'=>'fail']); 
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
        //echo "<pre>"; print_r($postData);exit;
           
        try {
            $checkpostData = DB::table('vehicles')->select('*')->where(['system_code'=>$input['system_code']])->count();

            if($checkpostData > 0){
                $postData['modified_at'] = date('Y-m-d H:i:s');
                $postData['modified_by'] = auth()->id();
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "Vehicle updated Successfully",
                    'data' => $returnPostData],200);
            }else{
                $postData['created_at'] = date('Y-m-d H:i:s');
                $postData['created_by'] = auth()->id();
                $response = response()->json([
                    'status' =>  "success",
                    'message' => "Vehicle created Successfully"
                ],200);
            }
            $res = Vehicles::updateOrCreate(['system_code'=>$input['system_code']], $postData);
                if($res){
                    return $response;
                    //return response()->json(['status'=>'success','message'=>'Vehicle Entry Successfully!','data'=>$res],200);
                   //return $response[] = ['status'=>'success', 'message'=>'Vehicle Entry Successfull', 'data'=>$res];
                } else {
                    return response()->json(['status'=>'error','message'=>'Something Wrong!','data'=>$res],200);
                  //return  $response[] = ['status'=>'error', 'message'=>'Something Wrong ', 'data'=>$res];
                }

        }
        catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function deleteVehicle(Request $request, $id){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = Vehicles::where('id', $id)->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Vehicle '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Vehicle']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getVehiclesByType(Request $request){
        
            $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicles')->select(['vehicles.*','vt.vehicle_type as vehicle_type','vt.slug as vehicle_type_slug', 'vm.name as vehicle_model', 'vms.name as vehicle_make'])
                   ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
                   ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')
                   ->join('vehicle_types as vt', 'vt.id', '=', 'vehicles.vehicle_type_id');
                   
                   
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
                   $searchQuery = " (t.fuel_type like '%".$searchValue."%' or t.vehicle_status like '%".$searchValue."%')";
                }
                // if(isset($postData['product_category']) && NULL !== $postData['product_category']){
                //     $query->where('pc.category_name', 'LIKE', $postData['product_category']);
                // } 
                // if(isset($postData['is_service']) && (NULL !== $postData['is_service'])){
                //     $query->where('pc.is_service', '=', $postData['is_service']);
                // } else {
                //     $query->where('pc.is_service', '=', '0');
                // } 
                if(isset($postData['vehicle_status']) && (NULL !== $postData['vehicle_status'])){
                    $query->where('vehicles.vehicle_status', 'LIKE', $postData['vehicle_status']);
                }
                /*if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vehicles.vehicle_type_id', 'LIKE', $postData['vehicle_type']);
                }*/
                if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehicle_type']);
                }
            }
            
        
         $query->get();

        //echo "<pre>";print_r($query);exit;
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
                 $companyName = '';
                if(Helper::getCompany()){
                    $companyName = Helper::getCompany().'files/';
                }
                 foreach($records as $key => $value){
                    if(NULL !== $value->images){
                        $records[$key]->images = URL('/public/upload/vehicle/images/').'/'.$value->images;
                        // $records[$key]->images = URL('/public/upload/vehicle/').'/'.$value->images;
                    }
                
                 }
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
    
    public function getVariationList(Request $request){
         $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicle_variations as vv')->select(['vv.*', 'vt.vehicle_type as vehicle_type'])
                   ->join('vehicle_types as vt', 'vt.id', '=', 'vv.vehicle_type_id');
                   
                   
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
                   $searchQuery = " (vv.name like '%".$searchValue."%' or vv.value like '%".$searchValue."%')";
                }
                if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehicle_type']);
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
                 
                 $data = array();
                    foreach($records as $recordKey => $record ){
                        
                       $data[] = array(
                        "sr_no" => $recordKey+1,
                        "id"=>$record->id,
                        "name"=>$record->name,
                        "value"=>$record->value,
                        "datatype"=>$record->datatype,
                        "is_active"=>$record->is_active,
                        "created"=>($record->created!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created)):'',
                        "modified"=>($record->modified!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified)):'',
                        'action'=>'Action'
                       ); 
                    }
                    
                    ## Response
                    $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $data
                    );
                 
                return response()->json(['status'=>'success','data'=>$response]);
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
    
    
    public function createVariation(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'value' => 'required',
            'vehicle_type_id' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors(), 'errorcode'=>409]);
        }
        
        $postData = $request->all();
        
        $insert['name'] = $postData['name'];
        $insert['value'] = $postData['value'];
        $insert['remark'] = $postData['remark'];
        $insert['datatype'] = $postData['datatype'];
        $insert['vehicle_type_id'] = $postData['vehicle_type_id'];
        $insert['is_active'] = $postData['is_active'];
        
        $checkpostData = DB::table('vehicle_variations')->select('*')->where(['name'=> $insert['name'],'value'=> $insert['value']])->count();
        if($checkpostData > 0){
            $res = 'Update';
            $insert['modified_at'] = date('Y-m-d H:i:s');
            $insert['modified_by'] = auth()->user()->id;
        }else{
            $res = 'Inserted'; 
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['created_by'] = auth()->user()->id;
        }
            
         //print_r($insert);exit;
        //$insertVehicleVariation = VehicleVariation::create($insert);
        try{
            //$insertVehicleVariation = VehicleVariation::create($insert);
            $insertVehicleVariation = VehicleVariation::updateOrCreate(['name'=> $insert['name'],'value'=> $insert['value']], $insert);
            
            if($insertVehicleVariation){
                return response()->json(['status'=>'success','message'=> 'Variation '.$res.' Successfully','data'=>$insertVehicleVariation]);
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
    
    public function deleteVariation(Request $request){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        //echo "<pre>"; print_r($postData['is_active']);exit;
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = VehicleVariation::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Variation '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Variation']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getFeatureList(Request $request){
         $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicle_features as vf')->select(['vf.*', 'vt.vehicle_type as vehicle_type'])
                   ->join('vehicle_types as vt', 'vt.id', '=', 'vf.vehicle_type_id');
                   
                   
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
                   $searchQuery = " (vf.type like '%".$searchValue."%' or vf.value like '%".$searchValue."%')";
                }
                if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehicle_type']);
                }
            }
            
        
         $query->get();


           $sql = $query;
                $records = $sql->count();
                $totalRecords = $records;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                // echo $sql3->toSql();exit;
                $records = $sql3->get();
                //return $records;
             if($records){
                 
                 $data = array();
                    foreach($records as $recordKey => $record ){
                        
                       $data[] = array(
                        "sr_no" => $recordKey+1,
                        "id"=>$record->id,
                        "type"=>$record->type,
                        "value"=>$record->value,
                        "datatype"=>$record->datatype,
                        "vehicle_type"=>$record->vehicle_type,
                        "is_active"=>$record->is_active,
                        "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
                        "modified_at"=>($record->modified_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->modified_at)):'',
                        'action'=>'Action'
                       ); 
                    }
                    
                    ## Response
                    $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $data
                    );
                 
                return response()->json(['status'=>'success','data'=>$response]);
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
    
    public function createFeature(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'value' => 'required',
            'vehicle_type_id' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors(), 'errorcode'=>409]);
        }
        
        $postData = $request->all();
        
        $insert['type'] = $postData['type'];
        $insert['value'] = $postData['value'];
        $insert['datatype'] = $postData['datatype'];
        $insert['vehicle_type_id'] = $postData['vehicle_type_id'];
        $insert['is_active'] = $postData['is_active'];
        
        $checkpostData = DB::table('vehicle_features')->select('*')->where(['type'=> $insert['type'],'value'=> $insert['value']])->count();
        if($checkpostData > 0){
            $res = 'Update';
            $insert['modified_at'] = date('Y-m-d H:i:s');
            $insert['modified_by'] = auth()->user()->id;
        }else{
            $res = 'Inserted'; 
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['created_by'] = auth()->user()->id;
        }
            
         //print_r($insert);exit;
        //$insertVehicleVariation = VehicleVariation::create($insert);
        try{
            //$insertVehicleVariation = VehicleVariation::create($insert);
            $insertVehicleFeature = VehicleFeature::updateOrCreate(['type'=> $insert['type'],'value'=> $insert['value']], $insert);
            
            if($insertVehicleFeature){
                return response()->json(['status'=>'success','message'=> 'Feature '.$res.' Successfully','data'=>$insertVehicleFeature]);
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
    
    public function deleteFeature(Request $request){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        //echo "<pre>"; print_r($postData['is_active']);exit;
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = VehicleFeature::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Feature '.$res.'d Successfully!']);
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
    
        
    public function getMakeList(Request $request){
         $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicle_manufacturers as vm')->select('vm.*');
                   
                   
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
                   $searchQuery = " (vm.name like '%".$searchValue."%' or vm.slug like '%".$searchValue."%')";
                }
                if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehicle_type']);
                }
            }
            
        
         $query->get();


           $sql = $query;
                $records = $sql->count();
                $totalRecords = $records;
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                // echo $sql3->toSql();exit;
                $records = $sql3->get();
             if($records){
                 
                 $data = array();
                    foreach($records as $recordKey => $record ){
                        
                       $data[] = array(
                        "sr_no" => $recordKey+1,
                        "id"=>$record->id,
                        "name"=>$record->name,
                        "slug"=>$record->slug,
                        "is_active"=>$record->is_active,
                        "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
                        "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
                        'action'=>'Action'
                       ); 
                    }
                    
                    ## Response
                    $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $data
                    );
                 
                return response()->json(['status'=>'success','data'=>$response]);
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
    
    public function createMake(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors(), 'errorcode'=>409]);
        }
        
        $postData = $request->all();
        
        $insert['name'] = $postData['name'];
        $insert['slug'] = $postData['slug'];
        $insert['is_active'] = $postData['is_active'];
        
        $checkpostData = DB::table('vehicle_manufacturers')->select('*')->where(['name'=> $insert['name'],'slug'=> $insert['slug']])->count();
        if($checkpostData > 0){
            $res = 'Update';
            $insert['modified_at'] = date('Y-m-d H:i:s');
            $insert['modified_by'] = auth()->user()->id;
        }else{
            $res = 'Inserted'; 
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['created_by'] = auth()->user()->id;
        }
            
         //print_r($insert);exit;
        //$insertVehicleVariation = VehicleVariation::create($insert);
        try{
            //$insertVehicleVariation = VehicleVariation::create($insert);
            $insertVehicleVariation = VehicleManufacturer::updateOrCreate(['name'=> $insert['name']], $insert);
            
            if($insertVehicleVariation){
                return response()->json(['status'=>'success','message'=> 'Variation '.$res.' Successfully','data'=>$insertVehicleVariation]);
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
    
    public function deleteMake(Request $request){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        //echo "<pre>"; print_r($postData['is_active']);exit;
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = VehicleManufacturer::where('id', $postData['id'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Variation '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Variation']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function getModelList(Request $request){
         $searchQuery = "";
            $response = array();
            $columnName = 'id';
            $columnSortOrder = "desc";
            $rowperpage = "-1";
            $draw = '1';
            $postData = $request->all();
            $query = DB::table('vehicle_model as vmo')->select('vmo.*','vm.name as vehicleMakeName')->join('vehicle_manufacturers as vm', 'vm.id', '=', 'vmo.make_id');//->get();
                   
                   
            $searchQuery = '';
             //print_r($query);exit;
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
                   $searchQuery = " (vmo.slug like '%".$searchValue."%' or vmo.value like '%".$searchValue."%' or vmo.name like '%".$searchValue."%' or vm.name like '%".$searchValue."%')";
                }
               /* if(isset($postData['vehicle_type']) && (NULL !== $postData['vehicle_type'])){
                    $query->where('vt.vehicle_type', 'LIKE', $postData['vehicle_type']);
                }*/
                if(isset($postData['vehicle_make_name']) && (NULL !== $postData['vehicle_make_name'])){
                    $query->where('vm.name', 'LIKE', $postData['vehicle_make_name']);
                }
            }
            
        
            $query->get();


            $sql = $query;
                $records = $sql->count();
                $totalRecords = $records;
               
                
                $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
                $sql3 = $query->whereRaw($searchQuery)->orderBy($columnName,$columnSortOrder);
                if ($rowperpage!='-1') {
                    $sql3->offset($start)->limit($rowperpage);
                }
                // echo $sql3->toSql();exit;
                $records = $sql3->get();
                //echo '<pre>';print_r($records);exit; 
             if($records){
                 
                 $data = array();
                    foreach($records as $recordKey => $record ){
                        
                       $data[] = array(
                        "sr_no" => $recordKey+1,
                        "id"=>$record->id,
                        "name"=>$record->name,
                        "slug"=>$record->slug,
                        "model_code"=>$record->model_code,
                        "oem"=>$record->oem,
                        "oem_sub_type"=>$record->oem_sub_type,
                        "is_three_wheeler"=>$record->is_three_wheeler,
                        "vehicleMakeName"=>$record->vehicleMakeName,
                        "is_active"=>$record->is_active,
                        "created_at"=>($record->created_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->created_at)):'',
                        "updated_at"=>($record->updated_at!=='0000-00-00 00:00:00')?date('d-m-Y', strtotime($record->updated_at)):'',
                        'action'=>'Action'
                       ); 
                    }
                    
                    ## Response
                    $response = array(
                       "draw" => intval($draw),
                       "iTotalRecords" => $totalRecordwithFilter,
                       "iTotalDisplayRecords" => $totalRecords,
                       "aaData" => $data
                    );
                 
                return response()->json(['status'=>'success','data'=>$response]);
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
    
    public function createModel(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors(), 'errorcode'=>409]);
        }
        
        $postData = $request->all();
        
        $insert['make_id'] = $postData['make_id'];
        $insert['vehicle_type_id'] = $postData['vehicle_type_id'];
        $insert['model_code'] = $postData['model_code'];
        $insert['name'] = $postData['name'];
        $insert['slug'] = Str::slug($postData['name']);
        $insert['oem'] = $postData['oem'];
        $insert['oem_sub_type'] = $postData['oem_sub_type'];
        $insert['model_master_id'] = $postData['model_master_id'];
        $insert['is_three_wheeler'] = $postData['is_three_wheeler'];
        $insert['is_active'] = $postData['is_active'];
        
        $checkpostData = DB::table('vehicle_model')->select('*')->where(['name'=> $insert['name'],'slug'=> $insert['slug']])->count();
        if($checkpostData > 0){
            $res = 'Update';
            $insert['modified_at'] = date('Y-m-d H:i:s');
            $insert['modified_by'] = auth()->user()->id;
        }else{
            $res = 'Inserted'; 
            $insert['created_at'] = date('Y-m-d H:i:s');
            $insert['created_by'] = auth()->user()->id;
        }
            
         //print_r($insert);exit;
        try{
            $insertVehicleModel = VehicleModel::updateOrCreate(['slug'=> $insert['slug']], $insert);
            
            if($insertVehicleModel){
                return response()->json(['status'=>'success','message'=> 'Vehicle Model '.$res.' Successfully','data'=>$insertVehicleModel]);
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
    
    public function deleteModel(Request $request){
        //echo 'Testimonial Delete';exit; 
        $postData = $request->post();
        if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
            $is_active = $postData['is_active'];
        } else {
            $is_active = 0;
        }
        //echo "<pre>"; print_r($postData['is_active']);exit;
        try{
            $res = $is_active == '1' ? 'Activate' : 'De-Activate';
            $delete = VehicleModel::where('slug', $postData['slug'])->update(['is_active' => $is_active]);
            //echo "<pre>"; print_r($delete);exit;
            if($delete){
                 return response()->json(['status'=>'success','message'=>'Vehicle Model '.$res.'d Successfully!']);
            } else {
                return response()->json(['status'=>'success','message'=>'Unable to '.$res.' Vehicle Model']);
            }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function UploadVehicleDocs(Request $request){
        $input = $request->all();
         
        //return $request->hasFile();
        if(!isset($input['slug']) || (NULL === $request->slug)){
            return response()->json(['status'=>'error', 'message'=>'Invalid Request']);
        }//return $input;
        // return strtoupper($input['slug']);
        $slug = strtoupper(str_replace('-', ' ', $input['slug']));
        //return $slug;
        $vehicle = DB::table('vehicles')->select(['vehicles.id'])
            ->join('vehicle_manufacturers as vms', 'vms.id', '=', 'vehicles.make_id')
            ->join('vehicle_model as vm', 'vm.id', '=', 'vehicles.model_id')
            ->orwhereRaw("CONCAT(REPLACE(vms.name, ' ', '-'), '-', REPLACE(vm.name, ' ', '-')) = ?",[$input['slug']])->first();
       
        if(!$vehicle){
             return response()->json(['status'=>'error', 'message'=>'Invalid Request!']);
        }
        
        $postData['vehicle_id'] = $vehicle->id;
        $response = [];
        $companyName = '';
        if(Helper::getCompany()){
            $companyName = Helper::getCompany().'files/';
        }
        //upload Image start
        
        if($request->hasFile('other_images')){
            $files = $request->file('other_images');
            foreach($files as $other_images){
            
                $original_filename = $other_images->getClientOriginalName();
                $original_filename_arr = explode('.', $original_filename);
                $file_ext = end($original_filename_arr);
                $file_type = $other_images->getMimeType();
                if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg'){
              
                	$other_image = $original_filename_arr[0].time(). '.'.$file_ext;
                    if ($other_images->move('./public/upload/vehicle/images', $other_image)) {
                        // if ($other_images->move('./public/upload/product/', $other_image)) {
                        $otherImage = [];
                        $otherImage['images'] = $other_image;
                        $otherImage['type'] = 'image';
                        $otherImage['vehicle_id'] =  $postData['vehicle_id'];
                        $otherImage['created_at'] = date('Y-m-d H:i:s');
                        $otherImage['created_by'] = auth()->user()->id;
                        
                        $otherImages = vehicleImage::create($otherImage);
                        if($otherImages){ $response['other_images'][]['success'] = 'Image Saved And Uploaded'; }
                        else { $response['other_images'][]['error'] = 'Image Saved But Not Uploaded'; }
                    } else { $response['other_images'][]['error'] = 'Image Not Saved'; }
                } else { $response['other_images'][]['error'] = 'Enter Valid Format'; }
            }
        }
        if($request->hasFile('featured_image')){
          
            $featured_image = $request->file('featured_image')->getClientOriginalName();
            $featured_image_arr = explode('.', $featured_image);
            $file_ext = end($featured_image_arr);
            $file_type = $request->file('featured_image')->getMimeType();
             //return $postData;
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$featureImage = $featured_image_arr[0].time(). '.'.$file_ext;
                if ($request->file('featured_image')->move('./public/upload/vehicle/images', $featureImage)) {
                    
                    $featuredImage['featured_image'] = $featureImage;
                    $featuredImage['modified_at'] = date('Y-m-d H:i:s');
                    $featuredImage['modified_by'] = auth()->user()->id;
                    
                    $featuredImages = Vehicles::where('id', '=', $postData['vehicle_id'])->update($featuredImage);
                    if($featuredImages){ $response['featured_image']['success'] = 'Featured Image Saved And Uploaded'; }
                    else { $response['featured_image']['error'] = 'Featured Image Saved But Unable To Upload'; }
                    
                } else { $response['featured_image']['error'] = 'Featured Image Not Saved'; }
            } else { $response['featured_image']['error'] = 'Enter Valid File Format'; }
        }
        if($request->hasFile('banner_image')){
       
            $banner_image = $request->file('banner_image')->getClientOriginalName();
            $banner_image_arr = explode('.', $banner_image);
            $file_ext = end($banner_image_arr);
            $file_type = $request->file('banner_image')->getMimeType();
             //return $postData;
            if($file_type == 'image/png' || $file_type == 'image/jpg' || $file_type == 'image/jpeg' || $file_type == 'image/webp'){
            	$banneImage = $banner_image_arr[0].time(). '.'.$file_ext;
                if ($request->file('banner_image')->move('./public/upload/vehicle/images', $banneImage)) {

                    $bannerImage['images'] = $banneImage;
                    $bannerImage['modified_at'] = date('Y-m-d H:i:s');
                    $bannerImage['modified_by'] = auth()->user()->id;
                    
                    $bannerImages = Vehicles::where('id', '=', $postData['vehicle_id'])->update($bannerImage);
                    if($bannerImages){ $response['banner_image']['success'] = 'Banner Image Saved And Uploaded'; }
                    else { $response['banner_image']['error'] = 'Banner Image Saved But Unable To Upload'; }
                    
                } else { $response['banner_image']['error'] = 'Banner Image Not Saved'; }
            } else { $response['banner_image']['error'] = 'Enter Valid File Format'; }
        }
        if($request->hasFile('document')){
          
            $original_filename = $request->file('document')->getClientOriginalName();
            $original_filename_arr = explode('.', $original_filename);
            $file_ext = end($original_filename_arr);
            $file_type = $request->file('document')->getMimeType();
            //  return $file_type;
            if($file_type == 'application/pdf'){
            	$document = $original_filename_arr[0].time(). '.'.$file_ext;
                if ($request->file('document')->move('./public/upload/vehicle/document', $document)) {
                    
                    $documentData['images'] = $document;
                    $documentData['type'] = 'document';
                    $documentData['vehicle_id'] =  $postData['vehicle_id'];
                    $documentData['created_at'] = date('Y-m-d H:i:s');
                    $documentData['created_by'] = auth()->user()->id;
                    
                    $document = vehicleImage::create($documentData);
                    if($document){ $response['document']['success'] = 'Document Saved And Uploaded'; }
                    else { $response['document']['error'] = 'Document Saved But Unable To Upload'; }
                    
                } else { $response['document']['error'] = 'Document Not Saved'; }
            } else { $response['document']['error'] = 'Enter Valid File Format'; }
        }
        
        
        
        if(count($response)>0){
            return response()->json(['status'=>'success', 'message'=>$response]);
        } else {
            return response()->json(['status'=>'error', 'message'=>'Please Upload Files']);
        }
        
        
        
        return response()->json($response);
        
        
    }
}