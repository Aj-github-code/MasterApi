<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

use DB;

class Setup extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'setup';
    }
    // protected $table = "setup";
    protected $fillable = ['id','type', 'module_name','config','created','modified', 'created_by', 'modified_by', 'is_active'];

    public $timestamps = false;
    
    public static function getCompanySetup($tblPrefix){
        return DB::table($tblPrefix.'_setup')
            ->select('config')->where(['type'=>'frontend', 'module_name'=>'website', 'is_active'=>true])->first();
    }
    
    public static function checkCompanySetup($tblPrefix){
        return DB::select("SHOW TABLES LIKE '".$tblPrefix."_setup'");
    }
}
