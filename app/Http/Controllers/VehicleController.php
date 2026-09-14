<?php

namespace App\Http\Controllers;


class VehicleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function carMake()
    {
        return view('carMake.index');
    }

    public function carMakeEdit($id)
    {
        return view('carMake.edit')->with('id', $id);
    }

    public function carMakeCreate()
    {
        return view('carMake.create');
    }

    public function carModel()
    {
        return view('carModel.index');
    }

    public function carModelEdit($id)
    {
        return view('carModel.edit')->with('id', $id);
    }

    public function carModelCreate()
    {
        return view('carModel.create');
    }

    public function vehicleType()
    {
        return view('vehicleType.index');
    }

    public function vehicleTypeEdit($id)
    {
        return view('vehicleType.edit')->with('id', $id);
    }

    public function vehicleTypeCreate()
    {
        return view('vehicleType.create');
    }

}
