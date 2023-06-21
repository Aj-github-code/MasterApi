<?php
namespace App\Helpers\TableMaker;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

use App\Helpers\Tables\AssignCampaign as AssignCampaign;
use App\Helpers\Tables\Campaign as Campaign;
use App\Helpers\Tables\CampaignAnswers as CampaignAnswers;
use App\Helpers\Tables\CampaignQuestions as CampaignQuestions;
use App\Helpers\Tables\Tags as Tags;
use App\Helpers\Tables\UserExams as UserExams;
use App\Helpers\Tables\UserExamResults as UserExamResults;
use App\Helpers\Tables\UserReportingManager as UserReportingManager;

class QNAMgmt{
    public function __construct($params = array()){
    }
    
    public static function create($prefix = NULL){

       try{
            // DB::beginTransaction();
            AssignCampaign::create($prefix.'assign_campaign');
            Campaign::create($prefix.'campaigns');
            CampaignAnswers::create($prefix.'campaign_answers');
            CampaignQuestions::create($prefix.'campaign_questions');
            Tags::create($prefix.'tags');
            UserExams::create($prefix.'user_exams');
            UserExamResults::create($prefix.'user_exam_results');
            UserReportingManager::create($prefix.'user_reporting_manager');
            // DB::commit();
        } catch(\Illuminate\Database\QueryException  $e) {
            // DB::rollBack();
        } catch(Exception $ex) {
            // DB::rollBack();
        }
       
    }
    
}