<?php

namespace App\Http\Controllers;


class PopularDestinationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('destinations.index');
    }
    
    public function create()
    {
        return view('destinations.create');
    }

    public function edit($id)
    {
        return view('destinations.edit')->with('id',$id);
    }

}