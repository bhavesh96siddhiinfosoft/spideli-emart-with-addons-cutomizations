<?php

namespace App\Http\Controllers;

class StoreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	public function index()
    {
        return view("stores.index");
    }

    public function create(){
        
        return view('stores.create');
    }
    
    public function edit($id)
    {
    	return view('stores.edit')->with('id',$id);
    }

    public function view($id)
    {
        return view('stores.view')->with('id',$id);
    }

}
