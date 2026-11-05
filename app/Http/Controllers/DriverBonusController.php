<?php

namespace App\Http\Controllers;

/**
 * Point 104 - driver bonuses and levels (docs/APP-SPEC-DRIVER-BONUSES.md).
 * The screens read and write Firestore themselves, like the rest of the
 * panel: driver_bonus_rules, settings/driverBonus and driver_bonus_results.
 */
class DriverBonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('driver_bonuses.index');
    }

    public function create()
    {
        return view('driver_bonuses.create');
    }

    public function edit($id)
    {
        return view('driver_bonuses.edit')->with('id', $id);
    }

    public function settings()
    {
        return view('driver_bonuses.settings');
    }

    public function results()
    {
        return view('driver_bonuses.results');
    }
}
