<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayoutRequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id="")
    {
        return view("payoutRequests.drivers.index")->with("id",$id);
    }

    public function vendor($id="")
    {
        return view("payoutRequests.vendor.index")->with("id",$id);
    }
    public function provider($id = "")
    {
        return view("payoutRequests.provider.index")->with("id", $id);
    }
    public function owner($id = "")
    {
        return view("payoutRequests.owner.index")->with("id", $id);
    }
    public function vendorDisbursements($id="")
    {
        return view("payoutRequests.vendor.disbursement_index")->with("id",$id);
    }
    public function driverDisbursements($id="")
    {
        return view("payoutRequests.drivers.disbursement_index")->with("id",$id);
    }
    public function providerDisbursements($id = "")
    {
        return view("payoutRequests.provider.disbursement_index")->with("id", $id);
    }
    public function ownerDisbursements($id="")
    {
        return view("payoutRequests.owner.disbursement_index")->with("id",$id);
    }
}
