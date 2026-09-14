<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($type)
    {
        if ($type == "sales") {
            return view('reports.sales-reports');
        }else if ($type == "tax") {
            return view('reports.tax-reports');
        }
    }
}

?>