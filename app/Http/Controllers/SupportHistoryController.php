<?php

namespace App\Http\Controllers;


class SupportHistoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()    {

        return view("support_history.inbox");
    }


}
