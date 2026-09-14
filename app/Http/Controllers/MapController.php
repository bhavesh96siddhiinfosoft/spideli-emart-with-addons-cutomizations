<?php

namespace App\Http\Controllers;


class MapController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function multivendor()
    {

        return view('map.multivendor');
    }

    public function parcel()
    {

        return view('map.parcel');
    }

    public function rental()
    {

        return view('map.rental');
    }

    public function cab()
    {

        return view('map.cab');
    }

}