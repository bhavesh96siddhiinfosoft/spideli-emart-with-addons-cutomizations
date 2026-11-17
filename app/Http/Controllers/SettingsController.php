<?php

namespace App\Http\Controllers;


class SettingsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function social()
    {
        return view("settings.app.social");
    }

    public function globals()
    {
        return view("settings.app.global");
    }

    public function cod()
    {
        return view('settings.app.cod');
    }

    public function stripe()
    {
        return view('settings.app.stripe');
    }

    public function mobileGlobals()
    {
        return view('settings.mobile.globals');
    }

    public function razorpay()
    {
        return view('settings.app.razorpay');
    }

    public function paypal()
    {
        return view('settings.app.paypal');
    }

    public function radiosConfiguration()
    {
        return view("settings.app.radiosConfiguration");
    }

    public function wallet()
    {
        return view('settings.app.wallet');
    }


    public function payfast()
    {
        return view('settings.app.payfast');
    }

    public function paystack()
    {
        return view('settings.app.paystack');
    }

    public function mercadopago()
    {
        return view('settings.app.mercadopago');
    }

    public function flutterwave()
    {
        return view('settings.app.flutterwave');
    }

    public function xendit()
    {
        return view('settings.app.xendit');
    }

    public function midTrans()
    {
        return view('settings.app.midTrans');
    }

    public function orangePay()
    {
        return view('settings.app.orangePay');
    }

    /**
     * #92: MyCoolPay Cameroun. Everything is saved by the page in
     * settings/mycoolpay_settings, like the other gateways. The callback token
     * a first version kept in payment_secrets/mycoolpay is passed on, so the
     * callback link already set in the MyCoolPay merchant space stays valid.
     */
    public function mycoolpay()
    {
        $existingToken = '';
        try {
            $doc = app(\App\Finance\Firestore::class)->get('payment_secrets/mycoolpay');
            $existingToken = (string)($doc['data']['callbackToken'] ?? '');
        } catch (\Throwable $e) {
            report($e);
        }
        // Each region's currency: MyCoolPay takes XAF (and EUR), so the page refuses other regions.
        return view('settings.app.mycoolpay', ['existingToken' => preg_match('/^[a-f0-9]{40}$/', $existingToken) ? $existingToken : '',
            'regionCurrencies' => self::regionCurrencies()]);
    }

    /** #92: MyPVIT Gabon (PVit). Everything is saved by the page in settings/mypvit_settings. */
    public function mypvit()
    {
        return view('settings.app.mypvit', ['regionCurrencies' => self::regionCurrencies()]);
    }

    /**
     * #92: Sebpay, one page per country: settings/sebpay_{country}_settings.
     * Cameroun pays in XAF, Guinée in GNF.
     */
    public function sebpay($country)
    {
        $countries = ['cm' => ['title' => trans('lang.sebpay_cm'), 'currency' => 'XAF'], 'gn' => ['title' => trans('lang.sebpay_gn'), 'currency' => 'GNF']];
        abort_unless(isset($countries[$country]), 404);
        return view('settings.app.sebpay', ['country' => $country, 'title' => $countries[$country]['title'],
            'currency' => $countries[$country]['currency'], 'regionCurrencies' => self::regionCurrencies()]);
    }

    /** #92: regionId => [name, currency code], for the gateways that take one currency only. */
    private static function regionCurrencies()
    {
        $out = [];
        try {
            foreach ((new \App\Finance\Regions(app(\App\Finance\Firestore::class)))->all() as $id => $r) {
                $out[$id] = ['name' => $r['name'] ?? $id, 'currency' => strtoupper((string)($r['currency'] ?? ''))];
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $out;
    }

    public function deliveryCharge()
    {
        return view('settings.app.deliveryCharge');
    }

    public function smsGateway()
    {
        return view('settings.app.smsGateway');
    }

    public function orderHistory()
    {
        return view('settings.app.orderHistory');
    }

    public function regionDefaults()
    {
        return view('settings.app.regionDefaults');
    }

    public function adminCommission()
    {
        return view('settings.app.adminCommission');
    }

    public function openaiSettings()
    {
        return view("settings.app.openaiSettings");
    }

    public function languages()
    {
        return view('settings.languages.index');
    }

    public function languagesedit($id)
    {
        return view('settings.languages.edit')->with('id', $id);
    }

    public function languagescreate()
    {
        return view('settings.languages.create');
    }

    public function carMake()
    {
        return view('settings.carMake.index');
    }

    public function carMakeEdit($id)
    {
        return view('settings.carMake.edit')->with('id', $id);
    }

    public function carMakeCreate()
    {
        return view('settings.carMake.create');
    }

    public function carModel()
    {
        return view('settings.carModel.index');
    }

    public function carModelEdit($id)
    {
        return view('settings.carModel.edit')->with('id', $id);
    }

    public function carModelCreate()
    {
        return view('settings.carModel.create');
    }

    public function vehicleType()
    {
        return view('settings.vehicleType.index');
    }


    public function vehicleTypeEdit($id)
    {
        return view('settings.vehicleType.edit')->with('id', $id);
    }

    public function vehicleTypeCreate()
    {
        return view('settings.vehicleType.create');
    }

    public function rentalvehicleType()
    {
        return view('rentalvehicleType.index');
    }

    public function rentalvehicleTypeEdit($id)
    {
        return view('rentalvehicleType.edit')->with('id', $id);
    }

    public function rentalvehicleTypeCreate()
    {
        return view('rentalvehicleType.create');
    }

    public function promos()
    {
        return view('settings.promos.index');
    }

    public function promosEdit($id)
    {
        return view('settings.promos.edit')->with('id', $id);
    }

    public function promosCreate()
    {
        return view('settings.promos.create');
    }

    public function complaints()
    {
        return view('complaints.index');
    }

    public function complaintsEdit($id)
    {
        return view('complaints.edit')->with('id', $id);

    }

    public function sos()
    {
        return view('sos.index');
    }

    public function sosEdit($id)
    {
        return view('sos.edit')->with('id', $id);

    }

    public function specialOffer()
    {
        return view('settings.app.specialDiscountOffer');
    }

    public function menuItems()
    {
        return view('settings.menu_items.index');
    }

    public function menuItems2()
    {
        return view('settings.menu_items.index_newbackup');
    }

    public function menuItemsCreate()
    {
        return view('settings.menu_items.create');
    }

    public function menuItemsEdit($id)
    {
        return view('settings.menu_items.edit')->with('id', $id);
    }

    public function rentalDiscount()
    {
        return view('rentalDiscount.index');
    }

    public function rentalDiscountEdit($id)
    {
        return view('rentalDiscount.edit')->with('id', $id);
    }

    public function rentalDiscountCreate()
    {
        return view('rentalDiscount.create');
    }

    public function homepageTemplate()
    {
        return view('homepage_Template.index');
    }

    public function rentalvehicle()
    {
        return view('rentalVehicle.index');
    }

    public function rentalVehicleView($id)
    {
        return view('rentalVehicle.view')->with('id', $id);
    }

    public function footerTemplate()
    {
        return view('footerTemplate.index');
    }

    public function emailTemplatesIndex()
    {
        return view('email_templates.index');
    }

    public function emailTemplatesSave($id = '')
    {

        return view('email_templates.save')->with('id', $id);
    }

    public function banners()
    {
        return view("settings.app.banners");
    }
    public function businessModel()
    {
        return view('settings.app.businessModel');
    }
    public function documentVerification()
    {
        return view('settings.app.documentVerificationSetting');
    }
    public function scheduleOrderNotification()
    {
        return view('settings.app.schedule_notification');
    }
    public function maintenanceSettings()
    {
        return view('settings.app.maintenance_settings');
    }
}
