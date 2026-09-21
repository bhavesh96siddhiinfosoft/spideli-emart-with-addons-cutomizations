<?php

namespace App\Http\Controllers;

class ServiceGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('service_groups.index');
    }

    public function create()
    {
        return view('service_groups.create');
    }

    public function edit($id)
    {
        return view('service_groups.edit')->with('id', $id);
    }
}
