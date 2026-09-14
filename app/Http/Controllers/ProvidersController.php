<?php

namespace App\Http\Controllers;

class ProvidersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("providers.index");
    }

    public function create()
    {
        return view("providers.create");
    }

    public function edit($id)
    {
        return view('providers.edit')->with('id', $id);
    }

    public function view($id)
    {
    	return view('providers.view')->with('id', $id);
    }
    public function providerChat($id)
    {
        return view('providers.chat', compact('id'));
    }

}


