<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class POSController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function pointOfSale(){
        return view("pos.index");
    }

    public function posOrder(){
        return view("pos.order_index");
    }
}
