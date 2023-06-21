<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\OrderDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;


class OrderController extends Controller
{
    
    public function index(Request $request){
        
    }
    
    public function create(Request $request){
        
        $postData = $request->all();

        
        $orders['invoice'] = $postData['invoice'];
        $orders['invoice_no'] = $postData['invoice_no'];
        $orders['order_code'] = $postData['order_code'];
        $orders['total_amt'] = $postData['total_amt'];
        $orders['user_details'] = json_encode($postData['user_details']);
        $orders['pickup_details'] = (isset($postData['pickup_details']) && (NULL !== $postData['pickup_details']))?json_encode($postData['pickup_details']):NULL;
        $orders['order_date'] = $postData['order_date'];
        $orders['delivery_date'] = $postData['delivery_date'];
        $orders['is_active'] = '1';
        $orders['created_by'] = auth()->user()->id;
        $orders['created_at'] = date('Y-m-d H:i:s');
        // print_r($orders);exit;
        $order = Order::create($orders);
        
        if(isset($postData['delivery_boy']) && (NULL !== $postData['delivery_boy'])){
            
            // $deliveryPerson = $postData['delivery_boy'];
            $user = User::select('users.id')->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
                        ->join('roles as r', 'r.id', '=', 'ur.role_id')
                        ->where('slug', 'LIKE', 'delivery-boy')
                        ->where('email', 'LIKE', $postData['delivery_boy'])
                        ->first();
                        // print_r($user);exit;
            if($user){
                $delivery =[];
                $delivery['delivery_boy_id'] = $user->id;
                $delivery['order_id'] = $order->id;
                $delivery['delivery_date'] = $orders['delivery_date'];
                $delivery['status'] = '1';
                $delivery['created_by'] = auth()->user()->id;
                $delivery['created_at'] = date('Y-m-d H:i:s');
                 
                $assignDelivery = Delivery::create($delivery);
            }
        }
        $res = true;
        
        
        if(isset($postData['order_details']) && (NULL !== $postData['order_details'])){
            foreach($postData['order_details'] as $odKey => $oDetails){
                $orderDetails = [];
                $orderDetails['order_id'] = $order->id;
                $orderDetails['product_details'] = json_encode($oDetails['products']);
                $orderDetails['order_detail_code'] = $oDetails['order_detail_code'];
                $orderDetails['qty'] = $oDetails['qty'];
                $orderDetails['price'] = $oDetails['price'];
                $orderDetails['is_active'] = '1';
                $orderDetails['created_by'] = auth()->user()->id;
                $orderDetails['created_at'] = date('Y-m-d H:i:s');
                // print_r($orderDetails);exit;
                $orderDetail = OrderDetail::create($orderDetails);
                if(!$orderDetail){
                    $res = false;
                }
            }
        }
        
        if($res){
            return response()->json(['status'=>'success', 'message'=>'Order Placed Successfully']);
        } else {
            return response()->json(['status'=>'error', 'message'=>'Unable to place order']);
        }
    }
    
    
  
}
?>