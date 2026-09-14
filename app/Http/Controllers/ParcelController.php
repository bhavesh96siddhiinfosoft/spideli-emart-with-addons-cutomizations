<?php

namespace App\Http\Controllers;

class ParcelController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("parcel_category.index");
    }

    public function create()
    {
        return view("parcel_category.create");
    }

    public function edit($id)
    {
        return view('parcel_category.edit')->with('id', $id);
    }

    public function parcelWeight()
    {
        return view("parcel_weight.index");
    }

    public function parcelCoupons()
    {
        return view("parcel_coupons.index");
    }

    public function parcelCouponsCreate()
    {
        return view("parcel_coupons.create");
    }

    public function parcelCouponsEdit($id)
    {
        return view('parcel_coupons.edit')->with('id', $id);
    }

    public function parcelOrders($id = '')
    {
        return view("parcel_orders.index")->with('id', $id);
    }

    public function parcelOrderEdit($id)
    {
        return view('parcel_orders.edit')->with('id', $id);
    }

    public function parcelOrdersOwners($id = '')
    {
        return view("parcel_orders.owner_index")->with('id', $id);
    }

}


