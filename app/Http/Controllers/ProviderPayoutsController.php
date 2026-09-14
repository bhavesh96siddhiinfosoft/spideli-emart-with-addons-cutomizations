<?php

namespace App\Http\Controllers;


class ProviderPayoutsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = '')
    {

        return view("provider_payouts.index")->with('id', $id);
    }

    public function create($id = '')
    {

        return view("provider_payouts.create")->with('id', $id);
    }

}
