<?php
namespace App\Helpers\TableMaker;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

use App\Helpers\Tables\DeliveryMgmt\Delivery as Delivery;
use App\Helpers\Tables\DeliveryMgmt\Order as Order;
use App\Helpers\Tables\DeliveryMgmt\OrderDetail as OrderDetail;
use App\Helpers\Tables\DeliveryMgmt\CashBook as CashBook;
use App\Helpers\Tables\DeliveryMgmt\CashBookLog as CashBookLog;

class DeliveryMgmt{
    public function __construct($params = array()){
    }
    
    public static function create($prefix = NULL){

       try{
            // DB::beginTransaction();
            Delivery::create($prefix.'delivery');
            Order::create($prefix.'orders');
            OrderDetail::create($prefix.'order_details');
            CashBook::create($prefix.'cash_book');
            CashBookLog::create($prefix.'cash_book_log');
            // DB::commit();
        } catch(\Illuminate\Database\QueryException  $e) {
            // DB::rollBack();
        } catch(Exception $ex) {
            // DB::rollBack();
        }
       
    }
    
    public static function delete($prefix = NULL){

       try{
            // DB::beginTransaction();
            Delivery::delete($prefix.'delivery');
            Order::delete($prefix.'orders');
            OrderDetail::delete($prefix.'order_details');
            CashBook::delete($prefix.'cash_book');
            CashBookLog::delete($prefix.'cash_book_log');
            // DB::commit();
        } catch(\Illuminate\Database\QueryException  $e) {
            // DB::rollBack();
        } catch(Exception $ex) {
            // DB::rollBack();
        }
       
    }
    
}