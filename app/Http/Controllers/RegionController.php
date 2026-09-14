<?php

namespace App\Http\Controllers;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('region.index');
    }

    public function create()
    {
        return view('region.create');
    }

    public function edit($id)
    {
        return view('region.edit')->with('id', $id);
    }

    public function backfill()
    {
        return view('region.backfill');
    }
}
