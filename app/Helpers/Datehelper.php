<?php
namespace App\Helpers;
use App\Helpers\Helper as Helper;
use Illuminate\Http\Request;
use Session;
use DateTime;

use DB;
class Datehelper{
    public static function getQuarterByMonth($monthNumber) {
      return floor(($monthNumber - 1) / 3) + 1;
    }
    
    public static function getQuarterDay($monthNumber, $dayNumber, $yearNumber) {
      $quarterDayNumber = 0;
      $dayCountByMonth = array();
    
      $startMonthNumber = ((self::getQuarterByMonth($monthNumber) - 1) * 3) + 1;
    
      // Calculate the number of days in each month.
      for ($i=1; $i<=12; $i++) {
        $dayCountByMonth[$i] = date("t", strtotime($yearNumber . "-" . $i . "-01"));
      }
    
      for ($i=$startMonthNumber; $i<=$monthNumber-1; $i++) {
        $quarterDayNumber += $dayCountByMonth[$i];
      }
    
      $quarterDayNumber += $dayNumber;
    
      return $quarterDayNumber;
    }
    
    public static function getCurrentQuarterDay() {
      return self::getQuarterDay(date('n'), date('j'), date('Y'));
    }
    
    public static function getQuarterStartDate(){
        $qtr_start = date("Y-".(ceil(date("n")/3))."-01");
        return $qtr_start;
    }
    
    public static function getQuarterEndDate(){
        $qtr_end = date("Y-".(ceil(date("n")/3)+3)."-01");
        return $qtr_end;
    }
    
    public static function get_this_quarter() {
        $current_month = date('m');
        $current_quarter_start = ceil($current_month/4)*3+1; // get the starting month of the current quarter
        $start_date = date("Y-m-d H:i:s", mktime(0, 0, 0, $current_quarter_start, 1, date('Y') ));
        $end_date = date("Y-m-d H:i:s", mktime(0, 0, 0, $current_quarter_start+3, 1, date('Y') ));
        // by adding or subtracting from $current_quarter_start within the mktime function you can get any quarter of any year you want.
        return array($start_date, $end_date);
    }
    
    /**
        * Compute the start and end date of some fixed o relative quarter in a specific year.
        * @param mixed $quarter  Integer from 1 to 4 or relative string value:
        *                        'this', 'current', 'previous', 'first' or 'last'.
        *                        'this' is equivalent to 'current'. Any other value
        *                        will be ignored and instead current quarter will be used.
        *                        Default value 'current'. Particulary, 'previous' value
        *                        only make sense with current year so if you use it with
        *                        other year like: get_dates_of_quarter('previous', 1990)
        *                        the year will be ignored and instead the current year
        *                        will be used.
        * @param int $year       Year of the quarter. Any wrong value will be ignored and
        *                        instead the current year will be used.
        *                        Default value null (current year).
        * @param string $format  String to format returned dates
        * @return array          Array with two elements (keys): start and end date.
        * get_dates_of_quarter();
        //return current quarter start and end dates
        
        get_dates_of_quarter(2);
        //return 2nd quarter start and end dates of current year
        
        get_dates_of_quarter('first', 2010, 'Y-m-d');
        //return start='2010-01-01' and end='2014-03-31'
        
        get_dates_of_quarter('current', 2009, 'Y-m-d');
        //Supposing today is '2014-08-22' (3rd quarter), this will return
        //3rd quarter but of year 2009.
        //return start='2009-07-01' and end='2009-09-30'
        
        get_dates_of_quarter('previous');
        //Supposing today is '2014-02-18' (1st quarter), this will return
        //return start='2013-10-01' and end='2013-12-31'
        */
        public static function get_dates_of_quarter($quarter = 'current', $year = null, $format = null)
        {
            if ( !is_int($year) ) {        
               $year = (new DateTime)->format('Y');
            }
            $current_quarter = ceil((new DateTime)->format('n') / 3);
            switch (  strtolower($quarter) ) {
            case 'this':
            case 'current':
               $quarter = ceil((new DateTime)->format('n') / 3);
               break;
        
            case 'previous':
               $year = (new DateTime)->format('Y');
               if ($current_quarter == 1) {
                  $quarter = 4;
                  $year--;
                } else {
                  $quarter =  $current_quarter - 1;
                }
                break;
        
            case 'first':
                $quarter = 1;
                break;
        
            case 'last':
                $quarter = 4;
                break;
        
            default:
                $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                break;
            }
            if ( $quarter === 'this' ) {
                $quarter = ceil((new DateTime)->format('n') / 3);
            }
            $start = new DateTime($year.'-'.(3*$quarter-2).'-1 00:00:00');
            $end = new DateTime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30) .' 23:59:59');
        
            return array(
                'start' => $format ? $start->format($format) : $start,
                'end' => $format ? $end->format($format) : $end,
            );
        }
}