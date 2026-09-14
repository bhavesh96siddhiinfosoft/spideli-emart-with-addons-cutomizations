<?php

namespace App\Http\Controllers;

use App\Models\VendorUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\Storage;
use Google\Client as Google_Client;
use Illuminate\Support\Facades\View;

class ProductController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth');
    }

    public function index($id='')
    {
   		return view("items.index")->with('id',$id);
    }

    public function edit($id)
    {
    	return view('items.edit')->with('id',$id);
    }

    public function create($id='')
    {
      return view('items.create')->with('id',$id);
    }

    public function createitem()
    {
      return view('items.create');
    }
    public function view($id)
    {
        return view('items.view')->with('id',$id);
    }

    public function addToCart(Request $request)
    {
        $variantInfo = json_decode($request->variant_info, true);
        $addons = json_decode($request->addons ?? '[]', true);
        $addonsTotal = (float) ($request->addons_total ?? 0);

        // Prepare addons titles only
        $extras = collect($addons)->pluck('title')->values()->toArray();

        $cart = session()->get('cart', []);        
        
        $productKey = $request->id;
        if (!empty($request->variant_info)) {
            $variant = json_decode($request->variant_info, true);
            if (isset($variant['variant_id'])) {
                $productKey .= '_' . $variant['variant_id'];
            }
        }

        if (isset($cart[$productKey])) {
            $cart[$productKey]['quantity'] += $request->quantity;
        } else {
            $cart[$productKey] = [
                           
                'id' => $request->id,
                'quantity' => $request->quantity,
                'stock_quantity' => $request->stock_quantity,
                'name' => $request->name,
                'original_base_price' => $request->original_base_price, 
                'price' => $request->price,
                'dis_price' => $request->dis_price,
                'discount' => $request->discount,
                'image' => $request->image,
                'item_price' => $request->item_price,
                'taxValue' => $request->taxValue,               
                'category_id' => $request->category_id,
                'decimal_degits' => $request->decimal_degits,
                'unit' => $request->unit,
                'variant_info' => [
                    'variant_id' => $variantInfo['variant_id'] ?? '',
                    'variant_sku' => $variantInfo['variant_sku'] ?? '',
                    'variant_price' => $variantInfo['variant_price'] ?? '',
                    'variant_qty' => $variantInfo['variant_qty'] ?? '',
                    'variant_image' => $variantInfo['variant_image'] ?? '',
                    'variant_options' => $variantInfo['variant_options'] ?? [],
                ],
                'extras' => $extras,
                'extras_price' => $addonsTotal,
                'taxSetting' => $request->taxSetting ? json_decode($request->taxSetting, true) : [],
            ];
        }

        $cart['restaurant_id'] = $request->restaurant_id;
        $cart['item'][$cart['restaurant_id']][$productKey] = $cart[$productKey];
        $cart['decimal_degits'] = $request->decimal_degits;
        $cart['taxScope'] = $request->taxScope;
        $cart['taxesByScope'] = $request->taxesByScope ?? [];
        $cart['taxSetting'] = $cart['taxScope'] == "order" ? $cart['taxesByScope']['order'] : [];
        $cart['packagingCharge'] = $request->packagingCharge ?? 0;
        $cart['platformCharge'] = $request->platformCharge ?? 0;
        $cart['currencyData'] = $request->currencyData ?? [];

        $commissionSettings = [
            'enabled' => $request->input('commission_enabled') === '1',
            'type' => $request->input('commission_type', 'percentage'),
            'value' => floatval($request->input('commission_value', 0)),
        ];
        $cart['commissionSettings'] = $commissionSettings;

        $cart = $this->calculateTax($cart);

        session()->put('cart', $cart);  
       
        $cartHtml = View::make('pos.cart_item')->render();

        $totalHtml = View::make('pos.cart_total', compact('commissionSettings'))->render();

        return response()->json([
            'html' => $cartHtml,
            'total' => $totalHtml
        ]);

    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
    
    public function remove($index)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            unset($cart['item'][$cart['restaurant_id']][$index]);
            session()->put('cart', $cart);
        }

        if (count($cart['item'][$cart['restaurant_id']]) == 0) {
            return response()->json(['empty' => true]);
        }

        $cart = $this->calculateTax($cart);
        session()->put('cart', $cart);

        $commissionSettings = $cart['commissionSettings'];
        $cartHtml  = View::make('pos.cart_item')->render();
        $totalHtml = View::make('pos.cart_total', compact('commissionSettings'))->render();

        return response()->json([
            'empty' => false,
            'html'  => $cartHtml,
            'total' => $totalHtml
        ]);
    }
   
    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $index = $request->index;

        $error = null;

        if (isset($cart[$index])) {

            $item = $cart[$index];
            $currentQty = $item['quantity'];

            $variantQty = $item['variant_info']['variant_qty'] ?? null;
            $productQty  = $item['stock_quantity'] ?? -1;

            // If variant selected -> use variant qty
            // Else use main product qty
            $stockQty = ($variantQty !== null && $variantQty !== '') 
                        ? $variantQty 
                        : $productQty;

            // Increase quantity
            if ($request->operation === 'plus') {

                if ($stockQty == -1 || $currentQty + 1 <= $stockQty) {
                    $cart[$index]['quantity']++;
                    $cart['item'][$cart['restaurant_id']][$index]['quantity']++;
                } else {
                    $error = "Only {$stockQty} items available in stock.";
                }
            }

            // Decrease quantity
            elseif ($request->operation === 'minus') {

                if ($currentQty > 1) {
                    $cart[$index]['quantity']--;
                    $cart['item'][$cart['restaurant_id']][$index]['quantity']--;
                }
            }

            // Save only if no validation error
            if (!$error) {
                session()->put('cart', $cart);
            }
        }

        $cart = $this->calculateTax($cart);
        session()->put('cart', $cart);

        $commissionSettings = $cart['commissionSettings'];
        $cartHtml = View::make('pos.cart_item')->render();
        $totalHtml = View::make('pos.cart_total', compact('commissionSettings'))->render();

        return response()->json([
            'html' => $cartHtml,
            'total' => $totalHtml,
            'error' => $error,
        ]);
    }

    function calculateTax($cart){

        $cart['taxBreakdownGrouped'] = [
            'item' => [],
            'order' => [],
            'packaging' => [],
            'platform' => []
        ];

        $restaurant_id = $cart['restaurant_id'];

        $itemSubtotal = $totalDiscount = $totalTax = $adminCommissionTotal = 0;

        // Item subtotal before discount
        foreach ($cart['item'][$restaurant_id] as $item) {
            $itemSubtotal += ($item['original_base_price'] + ($item['extras_price'] ?? 0)) * $item['quantity'];
        }
        
        // Calculate admin commission
        $commissionSettings = $cart['commissionSettings'];
        $commissionEnabled = $commissionSettings['enabled'] ?? false;
        $commissionType = $commissionSettings['type'] ?? 'percentage';
        $commissionValue = $commissionSettings['value'] ?? 0;
        if ($commissionEnabled && $commissionValue > 0) {
            if ($commissionType === 'percentage') {
                $adminCommissionTotal += $itemSubtotal * ($commissionValue / 100);
            } else { // Fixed
                foreach ($cart['item'][$restaurant_id] as $item) {
                    $adminCommissionTotal += $commissionValue * $item['quantity'];
                }
            }
        }
        $cart['adminCommissionTotal'] = $adminCommissionTotal;
        
        // Prepare admin-enabled product taxes
        $globalProductTaxes = [];
        foreach ($cart['taxesByScope']['product'] ?? [] as $tax) {
            if ($tax['enable'] ?? false) {
                $globalProductTaxes[$tax['id']] = $tax;
            }
        }

        // PRODUCT-LEVEL TAX
        if ($cart['taxScope'] === 'product') {
            foreach ($cart['item'] as $restaurantItemsKey => $restaurantItems) {
                foreach ($restaurantItems as $itemKey => $item) {
                    $itemGross = ($item['original_base_price'] + ($item['extras_price'] ?? 0)) * $item['quantity'];
                    $itemCommissionShare = ($itemSubtotal > 0) ? ($itemGross / $itemSubtotal) * $adminCommissionTotal : 0;
                    $itemDiscount = ($itemSubtotal > 0) ? ($itemGross / $itemSubtotal) * $totalDiscount : 0;
                    /*$itemTaxable = max(0, $itemGross - $itemDiscount);*/
                    $itemTaxable = max(0, ($itemGross + $itemCommissionShare) - $itemDiscount);
                    $itemTaxes = [];
                    foreach ($item['taxSetting'] ?? [] as $itemTax) {
                        if (($itemTax['scope'] ?? 'product') === 'product' && isset($globalProductTaxes[$itemTax['id']])) {
                            $adminTax = $globalProductTaxes[$itemTax['id']];
                            if ($adminTax['type'] === 'percentage') {
                                $taxAmount = $this->applyTax($itemTaxable, $adminTax);
                            } else {
                                $taxAmount = $adminTax['tax'] * $item['quantity'];
                            }
                            $totalTax += $taxAmount;
                            $cart['taxBreakdownGrouped']['item'][$adminTax['title']] =
                                ($cart['taxBreakdownGrouped']['item'][$adminTax['title']] ?? 0) + $taxAmount;
                            $itemTaxes[] = ($adminTax['type'] ?? 'percentage') === 'percentage'
                                ? "{$adminTax['title']} ({$adminTax['tax']}%)"
                                : "{$adminTax['title']} (" . $this->formatCurrency($taxAmount, $cart['currencyData']) . ")";
                        }
                    }
                    if (empty($itemTaxes)) {
                        $cart['taxBreakdownGrouped']['item']['none'] = ($cart['taxBreakdownGrouped']['item']['none'] ?? 0) + 0;
                    }
                    $cart['item'][$restaurantItemsKey][$itemKey]['taxLabel'] = implode(', ', array_unique($itemTaxes));
                }
            }
        }

        // ORDER-LEVEL TAX
        if ($cart['taxScope'] === 'order') {
            /*$orderTaxable = max(0, $itemSubtotal - $totalDiscount);*/
            $orderTaxable = max(0,($itemSubtotal + $adminCommissionTotal) - $totalDiscount);
            foreach ($cart['taxesByScope']['order'] ?? [] as $tax) {
                if ($tax['enable'] ?? true) {
                    $taxAmount = $this->applyTax($orderTaxable, $tax);
                    $totalTax += $taxAmount;
                    $cart['taxBreakdownGrouped']['order'][$tax['title']] =
                        ($cart['taxBreakdownGrouped']['order'][$tax['title']] ?? 0) + $taxAmount;
                }
            }
        }

        // PACKAGING, PLATFORM TAXES
        $extraScopes = ['packaging', 'platform'];
        foreach ($extraScopes as $scope) {
            $charge = $cart[$scope . 'Charge'] ?? 0;
            foreach ($cart['taxesByScope'][$scope] ?? [] as $tax) {
                if (!isset($cart['taxBreakdownGrouped'][$scope][$tax['title']])) {
                    $cart['taxBreakdownGrouped'][$scope][$tax['title']] = 0;
                }
                $taxAmount = ($charge > 0) ? $this->applyTax($charge, $tax) : 0;
                $totalTax += $taxAmount;
                $cart['taxBreakdownGrouped'][$scope][$tax['title']] += $taxAmount;
            }
        }

        $cart['totalTax'] = $totalTax;

        return $cart;
    }

    function applyTax($amount, $tax) {
        if (!$tax['enable']) return 0;
        if ($tax['type'] === 'percentage') {
            return ($amount * $tax['tax']) / 100;
        }
        if ($tax['type'] === 'fix') {
            return $tax['tax'];
        }
    }

    function formatCurrency($amount, $currency = []){
        $symbol = $currency['symbol'] ?? '';
        $decimals = $currency['decimal_degits'] ?? 2;
        $symbolAtRight = filter_var($currency['symbolAtRight'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $formatted = number_format($amount, $decimals);

        return $symbolAtRight
            ? $formatted . ' ' . $symbol
            : $symbol . $formatted;
    }
}
