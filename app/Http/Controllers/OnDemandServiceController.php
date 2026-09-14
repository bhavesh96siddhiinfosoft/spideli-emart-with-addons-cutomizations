<?php

namespace App\Http\Controllers;

class OnDemandServiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Category()
    {
        return view("OnDemandService.categories.index");
    }

    public function CategoryCreate()
    {
        return view("OnDemandService.categories.create");
    }

    public function CategoryEdit($id)
    {
        return view('OnDemandService.categories.edit')->with('id', $id);
    }
    
    public function Coupons($id='')
    {
        return view("OnDemandService.coupons.index")->with('id', $id);
    }

    public function CouponCreate($id='')
    {
        return view("OnDemandService.coupons.create")->with('id', $id);
    }

    public function CouponEdit($id)
    {
        return view('OnDemandService.coupons.edit')->with('id', $id);
    }

    public function Services($id='')
    {
        return view("OnDemandService.services.index")->with('id',$id);
    }

    public function ServicesCreate($id = '')
    {
        return view("OnDemandService.services.create")->with('id', $id);
    }

    public function ServicesEdit($id)
    {
        return view('OnDemandService.services.edit')->with('id', $id);
    }

    public function Bookings($id='')
    {
        return view("OnDemandService.bookings.index")->with('id', $id);
    }

    public function BookingsCreate($id = '')
    {
        return view("OnDemandService.bookings.create")->with('id', $id);
    }

    public function BookingsEdit($id = '', $pid = '', $aid = '', $rid = '')
    {
        return view('OnDemandService.bookings.edit')->with('id', $id)->with('pid', $pid)->with('aid', $aid)->with('rid', $rid);
    }

    public function BookingsPrint($id)
    {
        return view('OnDemandService.bookings.print')->with('id', $id);
    }

    public function Workers($id='')
    {
        return view("OnDemandService.workers.index")->with('id', $id);
    }

    public function WorkersCreate($id = '')
    {
        return view("OnDemandService.workers.create")->with('id', $id);
    }

    public function WorkersEdit($id)
    {
        return view('OnDemandService.workers.edit')->with('id', $id);
    }

    public function workerChat($id)
    {
        return view('OnDemandService.workers.chat', compact('id'));
    }
   
}


