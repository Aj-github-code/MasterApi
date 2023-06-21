<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Helpers\Helper as Helper;

class QNAMgmtTags extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = Helper::getCompany().'tags as tags';
    }
    protected $fillable = ['id','name','slug', 'is_active', 'created_at', 'created_by', 'modified_at', 'modified_by'];
    public $timestamps = false;
}
