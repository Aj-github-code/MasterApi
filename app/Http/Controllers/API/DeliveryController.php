<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\OrderDetail;

use App\Models\CashBook;
use App\Models\CashBookLog;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;

use App\Helpers\Helper as Helper;


class DeliveryController extends Controller
{

    public function dashboard(Request $request){
        
        $company = Helper::getCompany();
        
        $userData = auth()->user()->roles;
        $deliveryBoy = false;
        if($userData){
            foreach($userData as $uKey => $uValue){
                if($uValue->slug === 'delivery-boy'){
                    $deliveryBoy = true;
                }
            }
        }
        
        $dashboard = Delivery::select(DB::Raw('COUNT(delivery.status) as count, CASE delivery.status when "1" then "Pending Deliveries" when "2" then "Cancelled Deliveries" when "3" then "Completed Deliveries"  END as delivery_status, status as status_id'))
        ->join($company.'orders as o', 'o.id', '=', 'delivery.order_id')->where('o.is_active', '=', '1')->groupBy('delivery.status')->get();
        
        if($dashboard){
            return response()->json(['status'=>'success', 'message'=>'Dashboard Records', 'data'=>$dashboard]);
        } else {
            return response()->json(['status'=>'error', 'message'=>'No Records Found']);
        }
        
                
        // print_r($query);exit;
    }  
    
    
    public function getDeliveryList(Request $request){
        
        $company = Helper::getCompany();
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        
        $query = Delivery::select(['o.*', 'delivery.delivery_code',DB::Raw('CASE delivery.status when "1" then "Pending Deliveries" when "2" then "Cancelled Deliveries" when "3" then "Completed Deliveries"  END as delivery_status'), 'delivery.status'])
                ->join($company.'orders as o', 'o.id', '=', 'delivery.order_id');
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
                     $query->orWhere('user_details', 'LIKE',  '%"name":"'.$searchValue.'%');
                     $query->orWhere('user_details', 'LIKE',  '%"district":"'.$searchValue.'%');
                //   $searchQuery = ' (user_details like "%'..'%" or user_details like "%'.$searchValue.'%")';
                }
                
                if(isset($postData['status']) && (NULL !== $postData['status'])){
                    $query->where('delivery.status', '=',  $postData['status']);
                   
                }
                
                 if(isset($postData['location_name']) && (NULL !== $postData['location_name'])){
                     if( ! ($postData['location_name'] === 'All Location')){
                        //  print_r($postData['location_name']);exit;
                        $query->where('o.user_details', 'LIKE',  '%"district":"'.$postData['location_name'].'"%');
                     }
                   
                }
            }
            
             $query->where('o.is_active', '=', '1')->get();
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
            //     );
             if($records){
                 foreach($records as $key => $value){
                     
                     $details = [];
                     $orderDetails =  OrderDetail::where('order_id', '=', $value->id)->get();
                     foreach($orderDetails as $odKey => $detail){
                         
                         $details[$odKey]['product_details'] = json_decode($detail->product_details);
                         $details[$odKey]['qty'] = (NULL !== $detail->qty)?$detail->qty:'0';
                         $details[$odKey]['price'] = (NULL !== $detail->price)?$detail->price:'0';
                         $details[$odKey]['return_qty'] = (NULL !== $detail->return_qty)?$detail->return_qty:'0';
                         $details[$odKey]['order_detail_code'] = $detail->order_detail_code;
                         
                         
                         
                         
                     }
                     
                     $records[$key]->received_amt = (NULL !== $value->received_amt)?$value->received_amt:'0';
                     $records[$key]->total_amt = (NULL !== $value->total_amt)?$value->total_amt:'0';
                     $records[$key]->pending_amt = (NULL !== $value->pending_amt)?$value->pending_amt:'0';
                     
                     $records[$key]->details = $details;
                    
                     
                      $records[$key]->modified_at = (NULL !== $value->modified_at)?$value->modified_at:'';
                      $records[$key]->modified_by = (NULL !== $value->modified_by)?$value->modified_by:'';
                     $records[$key]->sr_no = ($key+1);
                     $records[$key]->drop_point_details = json_decode($value->user_details);
                      $records[$key]->pickup_details = json_decode($value->pickup_details);
                      unset($value->user_details);
                      unset($value->id);
                    //  $records[$key]->featured_image = URL('/public/upload/product/').'/'.((NULL !== $value->featured_image) ? $value->featured_image : $value->banner_image);
                 }
                return response()->json(['status'=>'success','data'=>$records]);
            }else{
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }

        
    }
    
    public function cancel(Request $request){
        
        $validator = Validator::make($request->all(), [
            'order_code' => 'required',
            'remark' => 'required',
            'status' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Invalid Request!']);   
        }
        
        // $update['order_code'] = $request->order_code;
        $update['remark'] = $request->remark;
        $update['status'] = $request->status;
        
        try{
            $orderUpdate = Order::where('order_code', 'LIKE', $request->order_code)->first();
            // print_r($orderUpdate->id);exit;
            if($orderUpdate){
                $query = Delivery::where('order_id', '=', $orderUpdate->id)->first(); 
                if($query){
                    $deliveryUpdate = $query->update($update);
                    if($deliveryUpdate){
                              return response()->json(['status'=>'success','message'=>'Delivery Cancelled']);  
                    } else {
                        return response()->json(['status'=>'error','message'=>'Something Went Wrong! Delivery Not Cancelled']);  
                    }
                } else {
                  return response()->json(['status'=>'error','message'=>'Delivery Not Available']);     
                }
            } else {
                 return response()->json(['status'=>'error','message'=>'Delivery not available']);  
            }
        }  catch(\Illuminate\Database\QueryException  $e) {
            $error = explode(':',$e->getMessage());
            return response()->json(['status'=>'error','message'=>$error]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
      
        
        
    }
    
    public function confirm(Request $request){
        $validator = Validator::make($request->all(), [
            'order_code' => 'required',
            'status' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>'Invalid Request!']);   
        }
         $validator = Validator::make($request->all(), [
            'total_amt' => 'required',
            'received_amt' => 'required',
            'pending_amt' => 'required',
            
        ]);
   
        if($validator->fails()){
            return response()->json(['status'=>'error','message'=>$validator->errors()]);   
        }
        
        $postData = $request->all();
        
        $input['total_amt'] = $postData['total_amt'];
        $input['received_amt'] = $postData['received_amt'];
        $input['pending_amt'] = $postData['pending_amt'];
        $input['payment_mode'] = isset($postData['payment_mode'])?$postData['payment_mode']:NULL;
        
        $updateOrder = Order::where('order_code', 'LIKE', $postData['order_code'])->first();
        $updateOrder->update($input);
        if($updateOrder){
            
            $delivery = Delivery::where('order_id', '=', $updateOrder->id)->update(['status'=>$postData['status'], 'remark'=>isset($postData['remark'])?$postData['remark']:NULL]);
            if(isset($postData['details']) && (NULL !== $postData['details'])){
                foreach($postData['details'] as $dKey => $dValue){
                    $updateOrderDetail['return_qty'] = $dValue['return_qty'];
                    $upateOrderDetail = OrderDetail::where('order_detail_code', 'LIKE', $dValue['order_detail_code'])->update($updateOrderDetail);
                }
            }
            $i = 0;
            $totalCashAmt = 0;
            if(isset($postData['payment_mode']) && (NULL !== $postData['payment_mode'])){
               foreach($postData['payment_mode'] as $pmKey => $pmValue){
                   
                   $cashBookLogs[$i]['order_code'] = $postData['order_code'];
                   $cashBookLogs[$i]['name'] = isset($postData['drop_point_details'])?$postData['drop_point_details']['name']:NULL;
                   $cashBookLogs[$i]['type'] = 'collect';
                   $cashBookLogs[$i]['payment_mode'] = $pmKey;
                   $cashBookLogs[$i]['amt'] = $pmValue;
                   $cashBookLogs[$i]['created_at'] = date('Y-m-d H:i:s');
                   if($pmKey === 'cash'){
                       $totalCashAmt += $pmValue;
                   }
                   $i++;
               } 
               
            } else {
                $cashBookLogs[$i]['order_code'] = $postData['order_code'];
                $cashBookLogs[$i]['name'] = isset($postData['drop_point_details'])?$postData['drop_point_details']['name']:NULL;
                $cashBookLogs[$i]['type'] = 'collect';
                $cashBookLogs[$i]['payment_mode'] = 'cash';
                $cashBookLogs[$i]['amt'] = $totalCashAmt = $postData['total_amt'];
                $cashBookLogs[$i]['created_at'] = date('Y-m-d H:i:s');   
            }
            
            $cashBookData = CashBook::where('user_id', '=', auth()->user()->id)->where('date', '=', date('Y-m-d'))->first();
            // print_r($cashBookData);exit;
            if($cashBookData){
                $cashBooks['user_id'] = auth()->user()->id;
                $cashBooks['closing_balance'] = $cashBookData->closing_balance + $totalCashAmt;
                $cashBook = $cashBookData->update($cashBooks);
            } else {
                $prevCashBook = CashBook::where('user_id', '=', auth()->user()->id)->where('date', '=', date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))))->first();
                if($prevCashBook){
                    $closingBalance = $prevCashBook->closing_balance + $totalCashAmt;
                    $openingBalance = $prevCashBook->closing_balance;
                } else {
                    $closingBalance = $totalCashAmt;
                    $openingBalance = 0;
                }
                $cashBooks['user_id'] = auth()->user()->id;
                $cashBooks['opening_balance'] = $openingBalance;
                $cashBooks['closing_balance'] = $closingBalance;
                $cashBooks['date'] = date('Y-m-d');
                $cashBookData = CashBook::create($cashBooks);
            }
            foreach($cashBookLogs as $key => $value){
                $cashBookLogs[$key]['cash_book_id'] = $cashBookData->id;
            }
            
            $cashBookLog = CashBookLog::insert($cashBookLogs);
            return response()->json(['status'=>'success','message'=>'Delivery Confirmed']);  
        } else {
            return response()->json(['status'=>'error','message'=>'Delivery Not Confirmed']);  
        }
        
        
        
      
     
        
    }
    
    
    public function cashbookList(Request $request){
           
        $searchQuery = "";
        $response = array();
        $columnName = 'id';
        $columnSortOrder = "desc";
        $rowperpage = "-1";
        $draw = '1';
        
            $query = CashBook::with('cashBookLog');
            $searchQuery = '1 = 1';
             if($_SERVER['REQUEST_METHOD']=='POST'){
                $postData = $request->post();
                // echo '<pre>';print_r($postData);exit;
                ## Read value
               $draw = (isset($postData['draw']) ? $postData['draw'] : '1');
                $start = (isset($postData['start']) ? $postData['start'] : '0');
                $rowperpage = (isset($postData['length']) ? $postData['length'] : '-1'); // Rows display per page
                $columnIndex = (isset($postData['order'][0]['column']) ? $postData['order'][0]['column'] : '1'); // Column index
                $columnName = (isset($postData['columns'][$columnIndex]['data']) ? $postData['columns'][$columnIndex]['data'] : "id") ; // Column name
                $columnSortOrder = (isset($postData['order'][0]['dir']) ?  $postData['order'][0]['dir'] : "asc"); // asc or desc
                $searchValue = (isset($postData['search']['value']) ?  $postData['search']['value'] : ""); // Search value
             
                if($searchValue != ''){
                  $searchQuery = ' (date like "%'.$searchValue.'%")';
                }
                
                if((isset($postData['from_date']) && (NULL !== $postData['from_date'])) && (isset($postData['to_date']) && (NULL !== $postData['to_date']))){
                    $query->where('date', '>=',  $postData['from_date'])->where('date', '<=',  $postData['to_date']);
                } else if(isset($postData['from_date']) && (NULL !== $postData['from_date'])) {
                    $query->where('date', '>=',  $postData['from_date'])->where('date', '<=',  date('Y-m-d'));
                } else {
                    $query->where('date', '>=',  date('Y-m-d'));
                }
            }
            
             $query->get();
            $sql = $query;
        //   return response()->json($query->get());
        //     return $sql;
        //      print_r($sql);exit;
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
            $data = array();
            if($records){
                $opening_balance = 0;
                $closing_balance = 0;
                foreach($records as $recordKey => $record ){
                    $data[] = array(
                                "sr_no" => $recordKey+1,
                                "opening_balance"=>$record->opening_balance,
                                "closing_balance"=>$record->closing_balance,
                                "date"=>$record->date,
                                "cash_book_list"=> $record->cashBookLog
                                
                            );
                    if($recordKey == 0){
                        $opening_balance = $record->opening_balance;
                    } 
                    $closing_balance = $record->closing_balance;
                }
                //return $data;
                ## Response
                if(count($data)<=0){
                    $cashBook = CashBook::where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->first();
                    $opening_balance = $cashBook->closing_balance;
                    $closing_balance = 0;
                    // print_r($cashBook);exit;
                }
                $response = array(
                   "draw" => intval($draw),
                   "iTotalRecords" => $totalRecordwithFilter,
                   "iTotalDisplayRecords" => $totalRecords,
                   "aaData" => $data,
                   "opening_balance"=>$opening_balance,
                   "closing_balance"=>$closing_balance,
                   "status"=>'success'
                );
                return response()->json($response);
             } else {
                 return response()->json(['status'=>'error', 'message'=>'No records Found']);
             }
    }
    
    
    public function depositCashBook(Request $request){
        
        $postData = $request->all();
        
        $cashBook = CashBook::where('user_id', '=', auth()->user()->id)->orderBy('id', 'desc')->first();
        
        if($cashBook){
            $input['cash_book_id'] = $cashBook->id;
            $input['type'] = 'deposit';
            $input['amt'] = $postData['amt'];
            $input['payment_mode'] = 'cash';
            $createCashBookLog = CashBookLog::create($input);
            if($createCashBookLog){
                $update['closing_balance'] = $cashBook->closing_balance - $postData['amt'];
                $updateCashBook = CashBook::where('id', '=', $cashBook->id)->update($update);
                if($updateCashBook){
                    return response()->json(['status'=>'success', 'message'=>'Cashbook Deposited']);
                } else {
                    return response()->json(['status'=>'error', 'message'=>'Cashbook Not Deposited']);
                }
            } else {
                return response()->json(['status'=>'error', 'message'=>'Something Went Wrong!']);
            }
            
        } else {
            return response()->json(['status'=>'error', 'message'=>'Cashbook Not Available']);
        }
        
    }
    
    public function stockSummary(Request $request){
        DB::enableQueryLog();
         $query = Delivery::with('orders');
        //->where('delivery_date', '>=' , date('Y-m-d H:i:s', strtotime('-3 days', strtotime(date('Y-m-d H:i:s')))));
        
        $isAdmin = false;
        foreach(auth()->user()->roles as $ukey => $uValue){
            if($uValue->id != 1){
                $isAdmin = true;
            }
        }
        if($isAdmin){
                $query->where('delivery_boy_id', '=', auth()->user()->id);
        }
        
       
        
        /* if(NULL!==$request->post('status')){
            $query->where('delivery.status', '=', $request->post('status'));
        }else{
            $query->where('delivery.status', '=', 1); //default pending
        }
        
        if(NULL!==$request->post('search')){
            $query->where('orders.user_details', 'like', "%\"name\":\"".trim($request->post('search'))."\"%");
            $query->orWhere('orders.user_details', 'like', "%\"contact_no\":\"".trim($request->post('search'))."\"%");
        }*/
  
        $deliveries = $query->get();
        
        //print_r(DB::getQueryLog());exit;
        
        $ByProduct = [];
        $ByUser = [];
        $partyWise = [];
        $itemWise = [];
        $sumTotalStock = 0;
        $sumReturnQty = 0;
        $sumBalanceStock = 0;

        if($deliveries){
            foreach($deliveries as $dKey => $dValue){
                if($dValue->orders){
                    foreach($dValue->orders as $oKey => $oValue){
                        $oUser = json_decode($oValue->user_details);
                        $userName = '';
                        if($oUser){
                           $userName = $oUser->name;
                        } else {
                            $userName = 'User Not Defined';
                        }
                        // print_r($dValue->orders);exit;
                        $uTotalStock = 0;
                        $uReturnQty = 0;
                        $uBalanceStock = 0;
                        if((NULL !== $oValue->orderDetails)){
                            foreach($oValue->orderDetails as $odKey => $odValue){
                                $odProduct = [];
                                $totalStock = 0;
                                $returnStock = 0;
                                $balanceStock = 0;
                                if(NULL !== $odValue->product_details){
                                    $odProduct = json_decode($odValue->product_details);
                                    $baseWeight = $odProduct->base_weight;
                                    $totalStock = $baseWeight*$odValue->qty;
                                    $returnStock = (NULL !== $odValue->return_qty)? $baseWeight *$odValue->return_qty  : 0;
                                    $balanceStock = $totalStock - $returnStock;
                  
                                    
                                    $sumTotalStock +=  $totalStock;
                                    $sumReturnQty += $returnStock;
                                    $sumBalanceStock += $balanceStock;
                                }
                                $productName = '';
                                if($odProduct){
                                     $productName = $odProduct->name;
                                } else {
                                     $productName = 'Product Not Defined';
                                }
                                
                                $uTotalStock +=  $totalStock;
                                $uReturnQty += $returnStock;
                                $uBalanceStock += $balanceStock;
                                
                                if(NULL!==$request->post('status')){
                                    if($request->post('status') === "1"){
                                        if(isset($ByProduct['stock'][$productName])){
                                            $ByProduct['stock'][$productName]['qty'] += $balanceStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $balanceStock;
                                            $ByProduct['stock'][$productName] = $productStock;
                                        }
                                    } else if($request->post('status') === "2"){
                                        if(isset($ByProduct['stock'][$productName])){
                                            $ByProduct['stock'][$productName]['qty'] += $returnStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $returnStock;
                                            $ByProduct['stock'][$productName] = $productStock;
                                        }
                                    } else if($request->post('status') === "3"){
                                        if(isset($ByProduct['stock'][$productName])){
                                            $ByProduct['stock'][$productName]['qty'] += $totalStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $totalStock;
                                            $ByProduct['stock'][$productName] = $productStock;
                                        }
                                    } else {
                                         if(isset($ByProduct['total_stock'][$productName])){
                                            $ByProduct['total_stock'][$productName]['qty'] += $totalStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $totalStock;
                                            $ByProduct['total_stock'][$productName] = $productStock;
                                        }
                                        if(isset($ByProduct['return_stock'][$productName])){
                                            $ByProduct['return_stock'][$productName]['qty'] += $returnStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $returnStock;
                                            $ByProduct['return_stock'][$productName] = $productStock;
                                        }
                                        if(isset($ByProduct['balance_stock'][$productName])){
                                            $ByProduct['balance_stock'][$productName]['qty'] += $balanceStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $balanceStock;
                                            $ByProduct['balance_stock'][$productName] = $productStock;
                                        }
                                      }
                                }else{
                                     if(isset($ByProduct['total_stock'][$productName])){
                                            $ByProduct['total_stock'][$productName]['qty'] += $totalStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $totalStock;
                                            $ByProduct['total_stock'][$productName] = $productStock;
                                        }
                                        if(isset($ByProduct['return_stock'][$productName])){
                                            $ByProduct['return_stock'][$productName]['qty'] += $returnStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $returnStock;
                                            $ByProduct['return_stock'][$productName] = $productStock;
                                        }
                                        if(isset($ByProduct['balance_stock'][$productName])){
                                            $ByProduct['balance_stock'][$productName]['qty'] += $balanceStock;
                                        } else {
                                            $productStock['details'] = $odProduct;
                                            $productStock['qty'] = $balanceStock;
                                            $ByProduct['balance_stock'][$productName] = $productStock;
                                        }
                                    
                                }
                                // if(isset($ByProduct['total_stock'][$productName])){
                                //     $ByProduct['total_stock'][$productName]['qty'] += $totalStock;
                                // } else {
                                //     $productStock['details'] = $odProduct;
                                //     $productStock['qty'] = $totalStock;
                                //     $ByProduct['total_stock'][$productName] = $productStock;
                                // }
                                // if(isset($ByProduct['return_stock'][$productName])){
                                //     $ByProduct['return_stock'][$productName]['qty'] += $returnStock;
                                // } else {
                                //     $productStock['details'] = $odProduct;
                                //     $productStock['qty'] = $returnStock;
                                //     $ByProduct['return_stock'][$productName] = $productStock;
                                // }
                                // if(isset($ByProduct['balance_stock'][$productName])){
                                //     $ByProduct['balance_stock'][$productName]['qty'] += $balanceStock;
                                // } else {
                                //     $productStock['details'] = $odProduct;
                                //     $productStock['qty'] = $balanceStock;
                                //     $ByProduct['balance_stock'][$productName] = $productStock;
                                // }
                                
                            }
                        }
                        
                        
                        if(NULL!==$request->post('status')){
                            if($request->post('status') === "1"){
                                $ByUser['stock'][$userName] = isset($ByUser['stock'][$userName])?$ByUser['stock'][$userName] + $uBalanceStock : $uBalanceStock;
                            } else if($request->post('status') === "2"){
                                $ByUser['stock'][$userName] = isset($ByUser['stock'][$userName])?$ByUser['stock'][$userName] + $uReturnQty : $uReturnQty;
                            } else if($request->post('status') === "3"){
                                $ByUser['stock'][$userName] = isset($ByUser['stock'][$userName])?$ByUser['stock'][$userName] + $uTotalStock : $uTotalStock;
                            } else {
                                $ByUser['total_stock'][$userName] = isset($ByUser['total_stock'][$userName])?$ByUser['total_stock'][$userName] + $uTotalStock : $uTotalStock;
                                $ByUser['return_stock'][$userName] = isset($ByUser['return_stock'][$userName])?$ByUser['return_stock'][$userName] + $uReturnQty : $uReturnQty;
                                $ByUser['balance_stock'][$userName] = isset($ByUser['balance_stock'][$userName])?$ByUser['balance_stock'][$userName] + $uBalanceStock : $uBalanceStock;
                            }
                        }else{
                            $ByUser['total_stock'][$userName] = isset($ByUser['total_stock'][$userName])?$ByUser['total_stock'][$userName] + $uTotalStock : $uTotalStock;
                            $ByUser['return_stock'][$userName] = isset($ByUser['return_stock'][$userName])?$ByUser['return_stock'][$userName] + $uReturnQty : $uReturnQty;
                            $ByUser['balance_stock'][$userName] = isset($ByUser['balance_stock'][$userName])?$ByUser['balance_stock'][$userName] + $uBalanceStock : $uBalanceStock;
                        }
                      
                    }
                }
            }
        }
        
        
        
        if(count($ByUser)>0){
            foreach($ByUser as $bKey => $stocks){
                foreach($stocks as $sKey => $sValue){
                    if($sValue > 0){
                        
                        $partyWise[$bKey][] = ['name'=> $sKey, 'value'=>$sValue]; 
                    }
                }
            }
        }
        
        if(count($ByProduct)>0){
            foreach($ByProduct as $pStockType => $pStocks){
                 foreach($pStocks as $pName => $pProduct){
                     $tProduct = [];
                    $tProduct = $pProduct['details'];
                    $tProduct->qty = $pProduct['qty'];
                 $itemWise[$pStockType][] = $tProduct;
                }
            }
        }
        

        
        $stockSummary['total_stock'] = $sumTotalStock;
        $stockSummary['return_stock'] = $sumReturnQty;
        $stockSummary['balance_stock'] = $sumBalanceStock;
        return response()->json(['party_wise'=>$partyWise, 'item_wise'=>$itemWise, 'stock_summary'=>$stockSummary]);
        //return response()->json(['party_wise'=>$partyWise, 'item_wise'=>$ByProduct, 'stock_summary'=>$stockSummary]);
        // print_r($ByUser);exit;
        
        //return response()->json(['data'=>$deliveries]);
    }
    
    public function areaList(){
        $company = Helper::getCompany();
        $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(user_details, '$.district')) as district
            FROM ".$company."orders JOIN ".$company."delivery as d ON d.order_id = orders.id where  JSON_UNQUOTE(JSON_EXTRACT(user_details, '$.district')) is not null ";
        
        if(auth()->user()->id != 1){
            $sql .= " AND d.delivery_boy_id = ".auth()->user()->id;
        }
        $sql .= " GROUP BY JSON_EXTRACT(user_details, '$.district'), d.delivery_boy_id ";
        $query = DB::select($sql);
            $areas = [];
            foreach($query as $area){
                $areas[] = $area->district;
            }
        return response()->json(['status'=>'success','arealist'=>$areas]);
    }
    
    
}
?>