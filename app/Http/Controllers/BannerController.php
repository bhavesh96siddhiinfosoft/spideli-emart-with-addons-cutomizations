<?php

namespace App\Http\Controllers;

class BannerController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("banners.index");
    }

     public function edit($id)
    {
    	return view('banners.edit')->with('id', $id);
    }

    public function create()
    {
        return view('banners.create');
    }

}


