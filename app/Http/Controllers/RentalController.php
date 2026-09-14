<?php

namespace App\Http\Controllers;

class RentalController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function rentalOrders($id = '')
    {
        return view("rental_orders.index")->with('id', $id);
    }

    public function rentalOrderEdit($id)
    {
        return view('rental_orders.edit')->with('id', $id);
    }

    public function rentalPackage()
    {
        return view('rental_packages.index');
    }

    public function rentalPackageCreate()
    {
        return view('rental_packages.create');
    }

    public function rentalPackageEdit($id)
    {
        return view('rental_packages.edit')->with('id', $id);
    }
    public function rentalOrdersOwner($id = '')
    {
        return view("rental_orders.owner_index")->with('id', $id);
    }

}


