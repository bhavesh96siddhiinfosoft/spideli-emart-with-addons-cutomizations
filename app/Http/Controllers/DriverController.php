<?php

namespace App\Http\Controllers;

class DriverController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view("drivers.index");
    }

    public function edit($id)
    {
    	return view('drivers.edit')->with('id', $id);
    }
     public function create()
    {
        return view('drivers.create');
    }

    public function view($id)
    {
    	return view('drivers.view')->with('id', $id);
    }

    public function DocumentList($id)
    {
        return view("drivers.document_list")->with('id', $id);
    }
    public function DocumentUpload($driverId, $id)
    {
        return view("drivers.document_upload", compact('driverId', 'id'));
    }

    public function driverChat($id)
    {
        return view('drivers.chat', compact('id'));
    }

    public function fleetDrivers()
    {
        return view("fleet_drivers.index");
    }

    public function fleetDriversCreate()
    {
        return view("fleet_drivers.create");
    }

    public function fleetDriversEdit($id)
    {
        return view("fleet_drivers.edit", compact('id'));
    }
    public function fleetDriversView($id)
    {
        return view("fleet_drivers.view", compact('id'));
    }    

}


