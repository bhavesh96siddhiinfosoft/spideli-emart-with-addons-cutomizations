<?php

namespace App\Http\Controllers;

/**
 * #113: the advert price per day for each zone (advertisement_prices/{zoneId}.prices.{regionId}), one amount per
 * region the zone serves, in that region's currency. A store pays (days x the price of its zone and region) once
 * the admin has approved its advert. The screen reads and writes Firestore from the page, like the other screens.
 */
class AdvertisementPricesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('advertisements.prices');
    }
}
