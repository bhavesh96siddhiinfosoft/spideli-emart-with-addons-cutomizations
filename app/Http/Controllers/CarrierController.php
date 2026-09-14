<?php

namespace App\Http\Controllers;

class CarrierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('carriers.index');
    }

    public function create()
    {
        return view('carriers.create');
    }

    public function edit($id)
    {
        return view('carriers.edit')->with('id', $id);
    }
}
