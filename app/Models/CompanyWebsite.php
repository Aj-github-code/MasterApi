<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyWebsite extends Model
{
    use HasFactory;
    protected $table;
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->table = 'company_websites';
        
    }
    
    protected $fillable = ['id','company_id', 'company_code','slug', 'website_code', 'website_name', 'logo', 'website_logo',
                            'domain', 'subdomain', 'owner_name','owner_primary_email','owner_secondary_email','owner_contact_1',
                            'owner_contact_2','meta_keyword','meta_title','meta_description','about_us','contact_us','status',
                            'is_active','created_at','created_by', 'modified_at','modified_by'];

    public $timestamps = false;
}