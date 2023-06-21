<?php

namespace App\Http\Controllers\API;
use App\Helpers\Number_to_word;

use App\Http\Controllers\Controller;
use App\Models\setup;
use App\Models\SocietymgmtFlatDetail;
use App\Models\SocietymgmtBill;
use App\Models\SocietymgmtBillDetail;
use App\Models\SocietymgmtAlliedService;
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
    protected $tblPrefix;
    public function __construct()
    {
        $this->tblPrefix = Helper::getCompany();
        
    }
    
    public function index(Request $request){
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        // try {
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
               $searchQuery = " (customer_name like '%".$searchValue."%' or email like '%".$searchValue."%' or invoice_no like '%".$searchValue."%' or contact_no like '%".$searchValue."%' or gst_no like '%".$searchValue."%')";
            }
         
        }
        $query->where('is_active', '=', '1')->get();
        
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
        
        if($records){
            foreach($records as $key => $detail){
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
        //echo $month." ".$fiscalYr." ".$encodedFiscalYr;
        //echo $firstDate." ".$lastDate;
        DB::enableQueryLog();
        $alreadyBilledFlats = json_decode(json_encode($this->getMonthWiseBillFlats($request, base64_encode($month))), true);
        
        //print_r($alreadyBilledFlats['original']['data']);exit;
        $excludeFlats = [];
        if($alreadyBilledFlats['original']['status']==="success"){
            $collection = collect($alreadyBilledFlats['original']['data']);
            $plucked = $collection->pluck('flat_detail_id');
            $excludeFlats[] = $plucked->all();
        }
        //exit;
        /*echo $lastDate;
        print_r($excludeFlats);*/
        $billingUnits = SocietymgmtFlatDetail::select('*')
            ->where('maintenance_start_date', '<=', $lastDate)
            ->whereNotIn('id', $excludeFlats[0])
            ->get();
            
        /*
        //condition based billing
        if(NULL!==$request->post('wing')){
            $billingUnits->where('wing', $request->post('wing'));
        }
        
        $billingUnits->get();*/
        //echo '<pre>';print_r($billingUnit);
        //print_r(DB::getQueryLog());
        $alliedServices = json_decode(json_encode(DB::table($this->tblPrefix.'allied_services')->get()), true);//SocietymgmtAlliedService::where('is_active')->get();
        $services = [];
        //echo '<pre>';print_r($alliedServices);
        /*foreach($alliedServices as $sKey=>$alliedService){
            //print_r($alliedService);
            $services[Str::slug($alliedService['category'])][] = $alliedService;
        }*/
        //echo '<pre>';print_r($services);
        
        $lastBill = json_decode(json_encode($this->getLastBill($request, $encodedFiscalYr)), true);
        //print_r($lastBill['original']['data']);
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
                'invoice_no' => $month."/".$invoiceNo['original']['data'],
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
                    'invoice_no' => $month."/".$invoiceNo['original']['data'],
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
        $fiscalYear = Helper::get_fiscal_year();
        if(NULL===$fiscalYr){
            $fiscalYear = base64_decode($fiscalYr);
        }
        $lastBill = SocietymgmtBill::select('invoice_no')
                ->where('invoice_no', 'LIKE', "%".$fiscalYr."%")
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
        $companyData = json_decode(json_encode((new CompanyController)->view($request, NULL)), true);
        
        if($companyData['original']['data']['company_address']){
            $address = $companyData['original']['data']['company_address'];
            if(!empty($address)){
                foreach($address as $aKey=>$add){
                    $state = States::find($add['state'])->select('state_name')->first();
                    $city = City::find($add['city'])->select('city_name')->first();
                    $companyData['original']['data']['company_address'][$aKey]['state'] = $state->state_name;
                    $companyData['original']['data']['company_address'][$aKey]['city'] = $city->city_name;
                }
            }
        }
        $companyData = (isset($companyData['original']['data']))?$companyData['original']['data']:[];
        //echo '<pre>';print_r($companyData);exit;
        $invoice['flat_details'] = json_decode($invoice['flat_details'], true);
        if(!empty($invoice['bill_details'])){
            foreach($invoice['bill_details'] as $dKey=>$detail){
                $invoice['bill_details'][$dKey]['product_details'] = json_decode($detail['product_details'], true);
            }
        }
        
        $setup = Setup::select('config')->where('type','application')->where('module_name', 'invoice')->first();
        //echo '<pre>';print_r($setup['config']);
        $invoiceConfig = json_decode($setup->config, true);  
        //echo '<pre>';print_r($invoiceConfig['invoice']['payment_method']['upi']);exit;
        if(NULL===$request->get('download')){
            //echo "hii";exit;
            return View('societymgmt/bill', compact('invoice', 'companyData','invoiceConfig'));
        }else{
            //echo "hello";exit;
            $pdf = PDF::loadView('societymgmt/bill', compact('invoice', 'companyData', 'invoiceConfig'));
            return $pdf->download($invoice["flat_details"]["owner_name"]." (".$invoice['invoice_no'].').pdf');
        }
        
    }
    
    
    public function generatePdfBillMultiple(Request $request, $billMonth=NULL){
    
        $monthlyBills = SocietymgmtBill::select('invoice_no')->where('invoice_no', 'Like', $billMonth.'%')->limit(10)->get();
        $bills = '';
        //echo $bills;
        foreach($monthlyBills as $mKey=>$bill){
            //$bills.='<div class="break">';
            $bills.=$this->generatePdfBill($request, base64_encode($bill->invoice_no));
            //$bills.='</div>';
        }
        $pdf = PDF::loadView($bills);
        return $pdf->download('invoice.pdf');
    }
}
?>