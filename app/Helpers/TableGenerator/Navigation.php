<?php
namespace App\Helpers\TableGenerator;
use Session;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Navigation{
    public function __construct($params = array())
    {
    }
    
    public static function create($tableName){
        Schema::create($tableName, function (Blueprint $table) {
            //`id`,  `module`, `is_active`, `created`, `modified`
            $table->increments('id');
            $table->string('link_type', 255);
            $table->integer('menu_id', $autoIncrement = false, $unsigned = false);
            $table->integer('parent_id', $autoIncrement = false, $unsigned = false)->default(1);
            $table->integer('target', $autoIncrement = false, $unsigned = false);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->longText('parameter')->nullable();
            $table->string('class', 255);
            $table->integer('priority', $autoIncrement = false, $unsigned = false);
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('has_dynamic_child')->default(1);
            $table->string('module', 255);
            $table->dateTime('created_at');
            $table->integer('created_by', $autoIncrement = false, $unsigned = false)->default(0);
            $table->dateTime('modified_at')->nullable();
            $table->integer('modified_by', $autoIncrement = false, $unsigned = false)->default(0);
        });
        
        DB::table($tableName)->insert(
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Home',
                'slug' => '#',
                'parameter' => NULL,
                'class' => '',
                'priority' => '1',
                'has_dynamic_child' => '0',
                'module' => '{"param":{},"module":"home"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
    
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'About Us',
                'slug' => 'about',
                'parameter' => NULL,
                'class' => '',
                'priority' => '2',
                'has_dynamic_child' => '1',
                'module' => '{"param":{},"module":"about_us"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
    
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Our Services',
                'slug' => 'service',
                'parameter' => NULL,
                'class' => '',
                'priority' => '4',
                'has_dynamic_child' => '0',
                'module' => '{"param":{},"module":"services"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Gallery',
                'slug' => 'gallery',
                'parameter' => NULL,
                'class' => '',
                'priority' => '5',
                'has_dynamic_child' => '0',
                'module' => '{"param":{},"module":"gallery"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Our Products',
                'slug' => 'product',
                'parameter' => NULL,
                'class' => '',
                'priority' => '3',
                'has_dynamic_child' => '1',
                'module' => '{"param":{},"module":"product_categories"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
    
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '1',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Contact Us',
                'slug' => '#',
                'parameter' => NULL,
                'class' => '',
                'priority' => '1',
                'has_dynamic_child' => '0',
                'module' => '{"param":{},"module":"contact_us"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
            ],
            [
                'link_type' => 'internal',
                'menu_id' => '2',
                'parent_id' => '0',
                'target' => '_self',
                'name' => 'Dashboard',
                'slug' => '#',
                'parameter' => NULL,
                'class' => '',
                'priority' => '1',
                'has_dynamic_child' => '0',
                'module' => '{"param":{},"module":"dashboard"}',
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => NULL,
                'created_by' => '0',
                'modified_by' => '0'
            ]
        
        );
    }
    
}