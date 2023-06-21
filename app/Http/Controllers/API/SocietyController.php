<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Http;
use App\Helpers\Number_to_word;
use App\Helpers\Datehelper;

use App\Http\Controllers\Controller;
use App\Models\Setup;
use App\Models\SocietymgmtFlatDetail;
use App\Models\SocietymgmtBill;
use App\Models\SocietymgmtBillDetail;
use App\Models\SocietymgmtAlliedService;
use App\Models\SocietymgmtMonthlyOutstanding;
use App\Models\SocietymgmtWing;
use App\Models\States;
use App\Models\City;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use DateTime;

use App\Helpers\Helper as Helper;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SocietyController extends Controller
{
    public $tblPrefix;
    public $companyInfo;
    public $companyFilePath;
    public $wings;
    public function __construct(Request $request)
    {
        $this->tblPrefix = Helper::getCompany();
        
        $this->companyData = json_decode(json_encode((new CompanyController)->view($request, (NULL!==$request->post('company_code'))?$request->post('company_code'):NULL)), true);
        if($this->companyData['original']['status']=="success" && !empty($this->companyData['original']['data'])){
            $this->companyFilePath = URL('public/upload/'.$this->companyData['original']['data']['sub_domain'].'_files/');
        }else{
            return response()->json(['status'=>'error','message'=>'Invalid Request']);
        }
        
        $this->companyInfo = $this->companyData['original']['data'];
        $wings = json_decode(json_encode($this->getWingDetails($request)), true);
        if($this->companyData['original']['status']=="success" && !empty($this->companyData['original']['data'])){
            $this->wings = $wings['original']['data'];
        }else{
            return response()->json(['status'=>'error','message'=>'Society Wings Configuration Missing']);
        }
        //echo '<pre>';print_r($this->wings);exit;
    }
    
    public function index(Request $request){
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        // try {
        //DB::enableQueryLog();
        $query = SocietymgmtBill::select('*');
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
               //$searchQuery = " (JSON_EXTRACT(user_details, '$.wing') = '%$searchValue%' or (JSON_EXTRACT(user_details, '$.owner_name') like '%$searchValue%' or invoice_no like '%$searchValue%' or invoice_date like '%$searchValue%' or billing_month = '$searchValue')";
            }
         
        }
        $query->where('is_active', '=', '1')->with('billDetails')->get();
        
        $sql = $query;
        $records = $sql->count();
        $totalRecords = $records;
        // echo $totalRecords;exit;
        
        $totalRecordwithFilter = $query->whereRaw($searchQuery)->count();
        $sql3 = $query->whereRaw($searchQuery)
        ->orderBy($columnName,$columnSortOrder);
        if ($rowperpage!='-1') {
            $sql3->offset($start)->limit($rowperpage);
        }
        // echo $sql3->toSql();exit;
        $records = $sql3->get();
        //print_r(DB::getQueryLog());
        if($records){
            foreach($records as $key => $detail){
                // $pdf = file_get_contents($this->generatePdfBill($request, base64_encode($detail->invoice_no)));
                // print_r($pdf);exit;
                $records[$key]->id = ($key+1);
                $records[$key]->user_details = json_decode($detail->user_details);
                $records[$key]->address = json_decode($detail->address);
                $records[$key]->amount_after_tax = number_format($detail->amount_after_tax,2);
                $records[$key]->amount_before_tax = number_format($detail->amount_before_tax,2);
                $records[$key]->grand_total = number_format($detail->grand_total,2);
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
        
    }
    
    public function generateBill(Request $request, $billMonth=NULL){
        
        $month = date('Y-m', strtotime('-1 month'));
        //echo $month;
        if(NULL!==$billMonth){
            $month = base64_decode($billMonth);
        }
        $firstDate = $month.'-01';
        $lastDate = date("Y-m-t", strtotime($firstDate));
        $fiscalYr = Helper::get_fiscal_year($firstDate);
        $encodedFiscalYr = base64_encode($fiscalYr);
        
        DB::enableQueryLog();
        $alreadyBilledFlats = json_decode(json_encode($this->getMonthWiseBillFlats($request, base64_encode($month))), true);
        
        $excludeFlats = [];
        if($alreadyBilledFlats['original']['status']==="success"){
            $collection = collect($alreadyBilledFlats['original']['data']);
            $plucked = $collection->pluck('flat_detail_id');
            $excludeFlats[] = $plucked->all();
        }
        
        $billingUnits = SocietymgmtFlatDetail::select('*')
            ->where('maintenance_start_date', '<=', $lastDate)
            ->whereNotIn('id', $excludeFlats[0])
            ->get();
            
        
        $alliedServices = json_decode(json_encode(DB::table($this->tblPrefix.'allied_services')->get()), true);//SocietymgmtAlliedService::where('is_active')->get();
        $services = [];
        
        $lastBill = json_decode(json_encode($this->getLastBill($request, $encodedFiscalYr)), true);
        
        $bills = [];
        $billDetails = [];
        $invoiceNo['original']['data'] = $lastBill['original']['data'];
        $counter = 0;
        foreach($billingUnits as $key=>$unit){
            if($unit->maintenance_amt<=0.00){
                continue;
            }
            $billFrom = $firstDate;
            $billTo = $lastDate;
            $maintenanceAmt = $unit->maintenance_amt;
            if($unit->maintenance_start_date>$firstDate){
                //if maintenance is between day of month
                $billFrom = $unit->maintenance_start_date;
                $datetime1 = new \DateTime($billFrom);
                $datetime2 = new \DateTime($lastDate);
                $interval = $datetime1->diff($datetime2);
                $days = ($interval->format('%a'))+1;
                
                //calculation of amt for maintenance
                
                $datetime3 = new \DateTime($firstDate);
                $datetime4 = new \DateTime($lastDate);
                $interval2 = $datetime3->diff($datetime4);
                $fullDays = ($interval2->format('%a'))+1;
                $perDayCost = $unit->maintenance_amt/$fullDays;
                
                $maintenanceAmt = round($perDayCost*$days, 2);
            }
            
            $datetime5 = new \DateTime($billFrom);
            $datetime6 = new \DateTime($billTo);
            $interval3 = $datetime5->diff($datetime6);
            $nod = $interval3->format('%a');
            $otherCharges = 0;
            $invoiceNo = json_decode(json_encode($this->createInvoiceNo($request, $encodedFiscalYr, $invoiceNo['original']['data'])), true);
            
            /*for($i=0;$i<=2;$i++){
                if(!empty($unit->vehicle_number_.($i+1)) && array_key_exists('other-charges', $services)){
                    //logic to calculate parking charges goes here
                }
            }*/
            $flatDetails = json_decode($unit);
            $bills[$counter] = [
                'flat_detail_id' => $unit->id,
                'flat_details' => json_encode($flatDetails),
                'invoice_no' => $fiscalYr."/".$invoiceNo['original']['data'],
                'invoice_date' => date('Y-m-d'),
                'billing_month' => $month,
                'bill_from' => $billFrom,
                'bill_to' => $billTo,
                'no_of_days' => $nod+1,
                'amt_before_tax' => $maintenanceAmt,
                'other_charges' => $otherCharges,
                'tax' => 0.00,
                'amt_after_tax' => $maintenanceAmt+$otherCharges,
                'status' => 'Pending',
            ];
            
            foreach($alliedServices as $sKey=>$alliedService){
                $qty = 1;
                $unitPrice = (float)($alliedService['slug']=='maintenance-charge')?$maintenanceAmt:$alliedService['cost'];
                $billDetails[$counter][] = [
                    'invoice_no' => $fiscalYr."/".$invoiceNo['original']['data'],
                    'product_id' => $alliedService['id'],
                    'product_details' => json_encode($alliedService),
                    'unit_price' => $unitPrice,
                    'qty' => $qty,
                    'amt' => $unitPrice*$qty,
                    'tax' => 0.00,
                    'amt_after_tax' => $unitPrice*$qty,
                    
                ];
            }
            $counter = $counter+1;
            
            
        }
        //echo '<pre>';print_r($bills);exit;
        $billEntry = SocietymgmtBill::insert($bills);
        if($billEntry){
            foreach($billDetails as $dKey=>$detail){
                $detailEntry = SocietymgmtBillDetail::insert($detail);
            }
        }
       
    }
    
    public function getMonthWiseBillFlats(Request $request, $billMonth=NUll){
        $month = date('Y-m');
        if(NULL!==$billMonth){
            $month = base64_decode($billMonth);
        }
        //echo $month;
        $flats = SocietymgmtBill::select('flat_detail_id')
                ->where('billing_month', 'LIKE', $month)->get();
        
        if($flats){
            return response()->json(['status'=>'success','data'=>$flats]);
        }else{
            return response()->json(['status'=>'failed']);
        }
        
    }
    
    public function getLastBill(Request $request, $fiscalYr = NULL){
        $fiscalYr = Helper::get_fiscal_year();
        //echo $fiscalYr
        if(NULL===$fiscalYr){
            $fiscalYr = base64_decode($fiscalYr);
        }
        $lastBill = SocietymgmtBill::select('invoice_no')
                ->where('invoice_no', 'LIKE', $fiscalYr."%")
                ->orderBy('id', 'desc')->first();
        
        if($lastBill){
            $string = explode("/", $lastBill->invoice_no);
            $invNo = $string[count($string)-1];
            return response()->json(['status'=>'success','data'=>$invNo]);
        }else{
            return response()->json(['status'=>'success','data'=>0]);
        }
        
    }
    
    public function createInvoiceNo(Request $request, $fiscalYr=NULL, $lastBill=0){
        $fiscalYear = Helper::get_fiscal_year();
        if(NULL===$fiscalYr){
            $fiscalYear = base64_decode($fiscalYr);
        }
        //echo $fiscalYear." ".($lastBill);
        $invNo = $lastBill+1;
        return response()->json(['status'=>'success','data'=>$invNo]);
    }
    
    public function generatePdfBill(Request $request, $invoiceNo){
                
        $invoice = SocietymgmtBill::with('billDetails')->where('invoice_no', 'LIKE', base64_decode($invoiceNo))->first()->toArray();
        $billMonth = $invoice['billing_month'];
        $companyData = $this->companyInfo;
        //echo '<pre>';print_r($companyData);exit;
        $invoice['flat_details'] = json_decode($invoice['flat_details'], true);
        if(!empty($invoice['bill_details'])){
            foreach($invoice['bill_details'] as $dKey=>$detail){
                $invoice['bill_details'][$dKey]['product_details'] = json_decode($detail['product_details'], true);
            }
        }
        $outstandingMonth = date('Y-m', strtotime($invoice['bill_from'].' -1 month'));
        //echo $outstandingMonth.'<br>';
        //print_r($invoice['flat_details']).'<br>';exit;
        $pastDues = SocietymgmtMonthlyOutstanding::select('amount')->where('flat_detail_id', $invoice['flat_details']['id'])->where('month', $outstandingMonth)->first()->toArray();
        $arrear = ($pastDues)?$pastDues['amount']:0.00;
        $setup = Setup::select('config')->where('type','application')->where('module_name', 'invoice')->first();
        //echo '<pre>';print_r($setup['config']);
        $invoiceConfig = json_decode($setup->config, true);  
        //echo '<pre>';print_r($invoiceConfig['invoice']['payment_method']['upi']);exit;
        $wings = $this->wings;
        $printtype = (NULL===$request->get('download'))?'download':"print";
        if(NULL===$request->get('download')){
            //echo "hii";exit;
            return View('societymgmt/bill', compact('invoice', 'companyData','invoiceConfig', 'arrear', 'wings', 'printtype'));
        }else{
            //$bills = View('societymgmt/header'); 
            $bills= View('societymgmt/bill', compact('invoice', 'companyData','invoiceConfig', 'arrear', 'wings', 'printtype'));
            //$bills.= View('societymgmt/footer', compact('billMonth'));
            //echo $bills;exit;
            $pdf = PDF::loadView($bills);
            return $pdf->download($invoice["flat_details"]["owner_name"]." (".$invoice['invoice_no'].').pdf');
        }
        
    }
    
    
    public function generatePdfBillMultiple(Request $request, $billMonth=NULL){
        if(NULL===$billMonth){
            $billMonth = date('Y-m', strtotime('-1 month'));
        }
        //echo $billMonth;
        $monthlyBills = SocietymgmtBill::select('invoice_no')->where('billing_month', 'Like', $billMonth)->get();
        //echo '<pre>';print_r($monthlyBills);exit;
        $bills = View('societymgmt/header');
        //echo $bills;
        foreach($monthlyBills as $mKey=>$bill){
            //$bills.='<div class="break">';
            $bills.=$this->generatePdfBill($request, base64_encode($bill->invoice_no));
            //$bills.='</div>';
        }
        $bills.=View('societymgmt/footer', compact('billMonth'));
        /*$pdf = PDF::loadView($bills);
        return $pdf->download($billMonth.'.pdf');*/
        echo $bills;
    }
    
    public function testpdf(Request $request, $billMonth=NULL){
        if(NULL===$billMonth){
            $billMonth = date('Y-m', strtotime('-1 month'));
        }
        //echo $billMonth;
        $invoices = SocietymgmtBill::select('invoice_no')->where('invoice_no', 'Like', $billMonth.'%')->limit(15)->get();
        //echo '<pre>';print_r($monthlyBills);exit;
        //$bills = View('societymgmt/header');
        $bills=View('societymgmt/test', compact('invoices'));
        //$bills.=View('societymgmt/footer', compact('billMonth'));
        /*$pdf = PDF::loadView($bills);
        return $pdf->download($billMonth.'.pdf');*/
        echo $bills;
        
    }
    
    
    public function getWingDetails(Request $request){
        
        $wingQuery = SocietymgmtWing::select('*')->get();
        $wings = [];
        foreach($wingQuery as $wKey=>$wing){
            $wings[$wing->wing] = $wing;
        }
        
        //return $wings;
        return response()->json(['status'=>'success','data'=>$wings]);
    }
    
    public function wingList(Request $request){
        
        try {

        //DB::enableQueryLog();
            $data = SocietymgmtWing::select('wing')->get();
            if($data){
                return response()->json(['status'=>'success','data'=>$data]);
            } else {
                return response()->json(['status'=>'error','message'=>'No Records']);
            }
   
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    
    public function getFloorsAndWings(Request $request){
        
        try{
            $query = SocietymgmtFlatDetail::where('is_active', '=', '1');
            if(NULL !== $request->wing){
                // echo $request->wing;
                // print_r($request->wing);exit;
                $query->where('wing', '=', $request->wing);
            }
        $society = $query->get();
        $data = [];
        if($society){
            foreach($society as $sKey => $sValue){
                $data[$sValue->wing.'|'.$sValue->flat_no] = $sValue->wing.'-'.$sValue->flat_no;
            }
            // print_r($data);exit;
            return response()->json(['status'=>'success','data'=>$data]);
        } else {
            return response()->json(['status'=>'error','message'=>'No Records']);   
        }
        
        } catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error[1]]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
    }
    
    public function view(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
        
        $invoice = SocietymgmtBill::with('billDetails')->where('invoice_no', 'LIKE', $request->post('invoice_no'))->with('billDetails')->first();
        return response()->json(['status'=>'success','data'=>$invoice]);
    }
    
    public function storePayment(Request $request){
        $validator = Validator::make($request->all(), [
            'payment_mode' => 'required',
            'invoice_no' => 'required',
            'amount' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Validation Error'.$validator->errors()]);   
        }
    }
   
}
?>




