<?php

namespace App\Http\Controllers;

class PickupPointController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('pickup_points.index');
    }

    public function create()
    {
        return view('pickup_points.create');
    }

    public function edit($id)
    {
        return view('pickup_points.edit')->with('id', $id);
    }

    /** Parcels received at one point - Spideli_upgrade.docx #21. */
    public function parcels($id)
    {
        return view('pickup_points.parcels')->with('id', $id);
    }
}
