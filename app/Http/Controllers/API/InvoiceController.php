<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\ProductCategories;
use App\Models\ProductProductCategory;
use App\Models\ProductImages;
use App\Models\setup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\VehicleManufacturer;
use App\Models\VehicleModel;
use App\Models\VehicleVariation;
use App\Models\VehicleFeature;
use App\Helpers\Helper as Helper;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class InvoiceController extends Controller
{
    public function index(Request $request){
         $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        // try {
           $query = Invoice::select('*');
           
            
         

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
    
    public function create(Request $request){
        
        $postData = $request->all();
        
        $input['customer_name'] = $userDetails['name'] = $postData['customer_name'];
        
        if(isset($postData['invoice_no']) && (NULL !== $postData['invoice_no'])){
            
            $input['invoice_no'] = $postData['invoice_no'];
        } else {
            $invNo = json_decode($this->generateInvoiceNo(), true);
            $input['invoice_no'] = $invNo['inv_no'];
        }
        $input['email'] = $userDetails['email'] = $postData['email'];
        $input['contact_no'] = $userDetails['contact_no'] = $postData['contact_no'];
        $input['gst_no'] = $userDetails['gst_no'] = isset($postData['gst_no'])?$postData['gst_no']:'';
        $input['date'] = $postData['date'];
        $input['address'] = $userDetails['address'] = json_encode(isset($postData['address'])?$postData['address']:NULL);
        $input['user_details'] = json_encode($userDetails);
        if(isset($postData['discount']) && (NULL !== $postData['discount'])){
            $input['discount'] = $postData['discount'];
        }
        $input['status'] = '1';
        $input['is_active'] = '1';
  
        $input['created_at'] = date('Y-m-d H:i:s');
        $input['modified_at'] = date('Y-m-d H:i:s');
        $input['created_by'] = auth()->user()->id;
        $input['modified_by'] = auth()->user()->id;
        
        $invoice = Invoice::updateOrCreate(['invoice_no'=>$input['invoice_no']],$input);
        if($invoice){
            $success = true;
            if($postData['invoice_details']){
                $totalAmtAfterTax = 0;
                $totalAmtBeforeTax = 0;
                
                foreach($postData['invoice_details'] as $idKey => $details){
                    $amtAfterTax = 0;
                    $amtBeforeTax = 0;
                    $invoiceDetails = [];
                    
                    $invoiceDetails['invoice_id'] = $invoice->id;
                    $invoiceDetails['product_id'] = isset($details['product_id'])?$details['product_id']:'';
                     $invoiceDetails['product_name'] = isset($details['product_name'])?$details['product_name']:'';
                    $invoiceDetails['hsn_code'] = $details['hsn_code'];
                    $invoiceDetails['description'] = $details['description'];
                    $invoiceDetails['qty'] = $details['qty'];
                    if(isset($details['unique_code']) && (NULL !== $details['unique_code'])){
                        $invoiceDetails['unique_code'] = $details['unique_code'];
                    } else {
                        $invoiceDetails['unique_code'] = Str::random(12);
                    }
                    $invoiceDetails['base_price'] = $details['base_price'];
                    $invoiceDetails['sgst'] = isset($details['sgst'])?$details['sgst']:'0';
                    $invoiceDetails['cgst'] = isset($details['cgst'])?$details['cgst']:'0';
                    $invoiceDetails['igst'] = isset($details['igst'])?$details['igst']:'0';
                    $invoiceDetails['gst'] = $invoiceDetails['sgst'] + $invoiceDetails['cgst'] + $invoiceDetails['igst'];
                    $amtBeforeTax = $details['base_price'] * $details['qty'];
                    $gstAmt = ($details['base_price'] *  $invoiceDetails['gst']) / 100;
          
                    
                    $amtAfterTax = ($gstAmt + $details['base_price']) * $details['qty'];
                    
                    $totalAmtBeforeTax = $totalAmtBeforeTax + $amtBeforeTax;
                    $totalAmtAfterTax = $totalAmtAfterTax + $amtAfterTax;
                    
                    $invoiceDetails['amount_before_tax'] = $amtBeforeTax;
                    $invoiceDetails['amount_after_tax'] = $amtAfterTax;
                    $invoiceDetails['is_active'] = '1';
                    $invoiceDetails['created_at'] = date('Y-m-d H:i:s');
                    $invoiceDetails['modified_at'] = date('Y-m-d H:i:s');
                    $invoiceDetails['created_by'] = auth()->user()->id;
                    $invoiceDetails['modified_by'] = auth()->user()->id;
                    
                    $invoiceDetail = InvoiceDetail::updateOrCreate(['unique_code'=>$invoiceDetails['unique_code']],$invoiceDetails);
                    if(!$invoiceDetail){
                       $success = false; 
                    }
                }
            }
        }
        if($success){
            $updateInvoice['amount_before_tax'] = $totalAmtBeforeTax;
            $updateInvoice['amount_after_tax'] = $totalAmtAfterTax;
            $updateInvoice['grand_total'] = $totalAmtAfterTax -  $invoice->discount;
            $updateInvoices = Invoice::where(['invoice_no'=>$invoice->invoice_no])->update($updateInvoice);
        //           $input['discount'] = $details['discount'];
        // $input['adjustment'] = $details['adjustment'];
        // $input['amount_before_tax'] = $details['cgst'];
        // $input['amount_after_tax'] = $details['cgst'];
            return response()->json(['status'=>'success', 'message'=>'Invoice Created Successfully']);
        } else {
            return response()->json(['status'=>'error', 'message'=>'Invoice Not Created Successfully']);
        }
        
        
    }
    
    public function show(Request $request){
        if(NULL === $request->invoice_no){
            return response()->json(['status'=>'error', 'message'=>'Invoice No Required']);
        }
        
        $invoice = Invoice::with('invoiceDetails')->where('invoice_no', 'LIKE', $request->invoice_no)->first();
        if($invoice){
            
                    $invoice['user_details'] = json_decode($invoice['user_details'], true);
                    $invoice['address'] = json_decode($invoice['address']);
            if(isset($invoice['invoice_details']) && (NULL !== $invoice['invoice_details'])){
                foreach($invoice['invoice_details'] as $iKey => $iDetail){
                    
                    $invoice['invoice_details'][$iKey]->id = ($key+1);
                    $invoice['invoice_details'][$iKey]->amount_after_tax = number_format($iDetail->amount_after_tax,2);
                    $invoice['invoice_details'][$iKey]->amount_before_tax = number_format($iDetail->amount_before_tax,2);
                    $invoice['invoice_details'][$iKey]->grand_total = number_format($iDetail->grand_total,2);
                }
            }
            return response()->json(['status'=>'success', 'message'=>'Invoice Details', 'data'=>$invoice]);
        } else {
            return response()->json(['status'=>'success', 'message'=>'No records found']);
        }
        
    }
    
    public function delete(Request $request){
        $postData = $request->all();
        if(!isset($postData['invoice_no'])){
            return response()->json(['status'=>'error', 'message'=>'Invoice No Required']);
        }
        
        
        $invoice = Invoice::where('invoice_no', 'LIKE', $postData['invoice_no'])->first();
        if($invoice){
       
            $res = 'De-Activated';
            if(isset($postData['is_active']) && (NULL !== $postData['is_active'])){
                if($postData['is_active'] === '1'){ $res = 'Activated'; }
                $deleteInvoice = $invoice->update(['is_active'=> $postData['is_active']]);
            } else {
                $deleteInvoice = $invoice->update(['is_active'=>'0']);
            }
            
            if($deleteInvoice){
                return response()->json(['status'=>'success', 'message'=>'Invoice '.$res.' Successfully']);
            } else{
                 return response()->json(['status'=>'success', 'message'=>'Invoice Not '.$res]);
            }
        } else {
            return response()->json(['status'=>'error', 'message'=>'No Records Found']);
        }
        
        
    }
    
    function generateInvoiceNo(){
        
        $invoiceCount = Invoice::invoiceCount();
        $set = '0';
        if(($invoiceCount > '9999') && ($invoiceCount < '99999')){
            $set = '';
        } else if(($invoiceCount > '999') && ($invoiceCount < '9999')){
            $set = '0';
        } else if(($invoiceCount > '99') && ($invoiceCount < '999')){
            $set = '000';
        } else if(($invoiceCount > '9') && ($invoiceCount < '99')){
            $set = '000';
        } else {
            $set = '0000';
        }
        
        $fiscalYr = Helper::get_fiscal_year();
        return response()->json(['status'=>'success', 'message'=>'Invoice Number generated', 'inv_no'=>'INV/'.$fiscalYr.'/'.$set.($invoiceCount+1)]);
        //return $invoiceNo = 'INV/'.$fiscalYr.'/'.$set.($invoiceCount+1);
    }
    
    public function downloadReport(Request $request){
                
        $invoice = Invoice::with('invoiceDetails')->where('invoice_no', 'LIKE', $request->invoice_no)->first();
        // return response()->json($invoice);
        $pdf = PDF::loadView('report', compact('invoice'));
        return $pdf->download('invoice.pdf');

    }
    
    public function downloadReport2(Request $request, $invoiceNo){
                
        $invoice = Invoice::with('invoiceDetails')->where('invoice_no', 'LIKE', base64_decode($invoiceNo))->first();
        if($invoice){
             $invoice['user_details'] = json_decode($invoice['user_details']);
             
        // return response()->json($invoice);
            $pdf = PDF::loadView('report', compact('invoice'));
            $fileName = 'public/Invoice'.$invoiceNo.'.pdf';
            // return $pdf->output();
            // print_r(__DIR__ .$fileName);exit;
            file_put_contents($fileName, $pdf->output());
            return response()->json(['status'=>'success', 'data'=>base64_encode(URL($fileName))]);
            return response()->json(['status'=>'success', 'data'=>URL($fileName)]);
            return $pdf->download('invoice.pdf');
        } else {
            return response()->json(['status'=>'success', 'message'=>'No records found']);
        }

    }
    
}
?>