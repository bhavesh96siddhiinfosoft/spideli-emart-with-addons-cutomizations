<?php

namespace App\Http\Controllers;

class CarrierController extends Controller
{
    /* Point 35, 9 Oct 2026: Carrier Management is reached from Owners for now.
     * Set to true to bring the Settings menu entry and the list page back. */
    public const LIST_ENABLED = false;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!self::LIST_ENABLED) {
            return redirect()->route('owners');
        }
        return view('carriers.index');
    }

    /**
     * `fromCompany` is the user id of a driver who registered as a COMPANY.
     *
     * Client bug report 02 point 19: a company that registers must appear under
     * carrier management. The six company drivers on 2 October already carry
     * between 8 and 11 of the 12 details a carrier needs - registration number,
     * operating licence, unique id and their files, name, email, phone, country
     * and region - so the form is prefilled from the driver rather than retyped.
     *
     * WHAT CANNOT BE PREFILLED IS THE RATE CARD. Base, per km, per kg and the
     * minimum are a commercial decision, and a carrier with no rates cannot
     * price anything, so creating one automatically would produce a carrier
     * that looks ready and quotes nothing. An administrator sets them here.
     */
    public function create()
    {
        return view('carriers.create')
            ->with('fromCompany', request('fromCompany', ''))
            ->with('back', request('back', ''));
    }

    public function edit($id)
    {
        return view('carriers.edit')
            ->with('id', $id)
            ->with('fromCompany', request('fromCompany', ''))
            ->with('back', request('back', ''));
    }
}
