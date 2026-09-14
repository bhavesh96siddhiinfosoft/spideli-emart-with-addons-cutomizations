<?php

namespace App\Http\Controllers;

class VendorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	public function index()
    {
        return view("vendors.index");
    }

    public function create(){

        return view('vendors.create');  
    }

    public function edit($id)
    {
    	return view('vendors.edit')->with('id',$id);
    }
    
    public function DocumentList($id)
    {
        return view("vendors.document_list")->with('id', $id);
    }

    public function DocumentUpload($ownerId, $id)
    {
        return view("vendors.document_upload", compact('ownerId', 'id'));
    }
    public function vendorChat($id)
    {
        return view("vendors.chat", compact( 'id'));
    }
}
