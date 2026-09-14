<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index($id = null, $type = null)
	{
		$id = $id ?? @$_COOKIE['section_id'];
		$type = $type ?? @$_COOKIE['service_type'];
		
		switch ($type) {
			case "cab-service":
				return view('dashboard.cab', compact('id','type'));
			case "delivery-service":
				return view('dashboard.delivery', compact('id','type'));
			case "ecommerce-service":
				return view('dashboard.ecommerce', compact('id','type'));
			case "parcel_delivery":
				return view('dashboard.parcel', compact('id','type'));
			case "rental-service":
				return view('dashboard.rental', compact('id','type'));
			case "ondemand-service":
				return view('dashboard.ondemand', compact('id','type'));
			default:
				return view('dashboard.delivery', compact('id','type'));
		}
	}

	public function storeFirebaseService(Request $request)
	{
		if (!empty($request->serviceJson) && !Storage::disk('local')->has('firebase/credentials.json')) {
			Storage::disk('local')->put('firebase/credentials.json', file_get_contents(base64_decode($request->serviceJson)));
		}
	}
}
