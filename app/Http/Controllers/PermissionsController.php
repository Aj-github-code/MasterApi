<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller as BaseController;

class PermissionsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    // public function index()
    // {   
    //     $permissions = Permission::all();

    //     return view('permissions.index', [
    //         'permissions' => $permissions
    //     ]);
    // }

    /**
     * Show form for creating permissions
     * 
     * @return \Illuminate\Http\Response
     */
    public function create() 
    {   
        return view('permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $request->validate([
            'name' => 'required|unique:users,name'
        ]);

        if($validator->fails()){
            return response()->json(['status'=>'error', 'message'=>'Validation Error Occurred :'.$validator->errors()]);
        }

        try{
           $permission = Permission::create($request->only('name'));
            if($permission){
                return response()->json(['status'=>'success','message'=>'Permission Created Successfully']);
            } else {
                return response()->json(['status'=>'error','message'=>'Something went wrong']);
            }
        } catch(\Illuminate\Database\QueryException  $e) {
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        } catch(Exception $ex) {
            return response()->json(['status'=>'error','message'=>$ex->getMessage()]);
        }
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Permission  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        return view('permissions.edit', [
            'permission' => $permission
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permission->id
        ]);

        $permission->update($request->only('name'));

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permission updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permission deleted successfully.'));
    }
}