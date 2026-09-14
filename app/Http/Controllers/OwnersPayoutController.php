<?php

namespace App\Http\Controllers;


class OwnersPayoutController extends Controller
{

   public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index($id = '')
    {      
       return view("owners_payouts.index")->with('id', $id);       
    }    
    public function create($id = '')
    {        
       return view("owners_payouts.create")->with('id', $id);
    }

}
