@php
$itemSubtotal = 0;
$adminCommissionTotal = 0;
$cart = session('cart', []);

$commissionEnabled = $commissionSettings['enabled'] ?? false;
$commissionType = strtolower($commissionSettings['type'] ?? 'percentage');
$commissionValue = $commissionSettings['value'] ?? 0;

// calculate subtotal
foreach ($cart as $item) {
    $variantPrice = $item['variant_info']['variant_price'] ?? 0;
    $basePrice = $variantPrice > 0 ? $variantPrice : ($item['original_base_price'] ?? 0);
    $extrasPrice = $item['extras_price'] ?? 0;
    $quantity = $item['quantity'] ?? 1;

    $itemTotal = ($basePrice + $extrasPrice) * $quantity;
    $itemSubtotal += $itemTotal;
}

// calculate admin commission
if ($commissionEnabled && $commissionValue > 0) {
    if ($commissionType === 'percentage') {
        $adminCommissionTotal = $itemSubtotal * ($commissionValue / 100);
    } else { // fixed
        $adminCommissionTotal = $commissionValue;
    }
    $adminCommissionTotal = round($adminCommissionTotal, 2);
}

// total including admin commission
$taxableSubtotal = $itemSubtotal + $adminCommissionTotal + $cart['packagingCharge'] + $cart['platformCharge'];
$grandTotal = $taxableSubtotal + $cart['totalTax'];

@endphp

<!-- Subtotal (items only) -->
<tr>
    <td colspan="2"><strong>{{ trans('lang.sub_total') }}</strong></td>
    <td colspan="2" class="text-right">
        <strong>
            <span class="cart-price" data-price="{{ number_format($itemSubtotal, 2, '.', '') }}">
                {{ number_format($itemSubtotal, 2) }}
            </span>
        </strong>
    </td>
</tr>

<!-- Admin Commission -->
@if($commissionEnabled && $adminCommissionTotal > 0)
<tr>
    <td colspan="2">
        {{ trans('lang.admin_commission') }}
        @if($commissionType === 'percentage') ({{ $commissionValue }}%) @else ({{ trans('lang.coupon_fixed') }}) @endif
    </td>
    <td colspan="2" class="text-right">
        <span class="cart-price" data-price="{{ number_format($adminCommissionTotal, 2, '.', '') }}">
            {{ number_format($adminCommissionTotal, 2) }}
        </span>
    </td>
</tr>
@endif

<!-- Packaging Charge -->
@if($cart['packagingCharge'] && $cart['packagingCharge'] > 0)
<tr>
    <td colspan="2">
        {{ trans('lang.packaging_charge') }}
    </td>
    <td colspan="2" class="text-right">
        <span class="cart-price" data-price="{{ number_format($cart['packagingCharge'], 2, '.', '') }}">
            {{ number_format($cart['packagingCharge'], 2) }}
        </span>
    </td>
</tr>
@endif

<!-- Platform Charge -->
@if($cart['platformCharge'] && $cart['platformCharge'] > 0)
<tr>
    <td colspan="2">
        {{ trans('lang.platform_charge') }}
    </td>
    <td colspan="2" class="text-right">
        <span class="cart-price" data-price="{{ number_format($cart['platformCharge'], 2, '.', '') }}">
            {{ number_format($cart['platformCharge'], 2) }}
        </span>
    </td>
</tr>
@endif

<!-- Taxes -->
@if(!empty($cart['taxBreakdownGrouped']))

{{-- Item-level --}}
@if(!empty($cart['taxBreakdownGrouped']['item']))
    <tr>
        <td colspan="2">
            {{ trans('lang.tax_on_item_total') }}:
        </td>
        <td colspan="2" class="text-right">
            <span class="cart-price" data-price="{{ number_format(array_sum($cart['taxBreakdownGrouped']['item']), $cart['decimal_degits'] ?? 2) }}">
                {{ number_format(array_sum($cart['taxBreakdownGrouped']['item']), $cart['decimal_degits'] ?? 2) }}
            </span>
        </td>
    </tr>
@endif

{{-- Order-level --}}
@if(!empty($cart['taxBreakdownGrouped']['order']))
    <tr>
        <td colspan="2">
            {{ trans('lang.tax_on_order_total') }}:
        </td>
        <td colspan="2" class="text-right">
            <span class="cart-price" data-price="{{ number_format(array_sum($cart['taxBreakdownGrouped']['order']), $cart['decimal_degits'] ?? 2) }}">
                {{ number_format(array_sum($cart['taxBreakdownGrouped']['order']), $cart['decimal_degits'] ?? 2) }}
            </span>
        </td>
    </tr>
@endif

{{-- Packaging-level --}}
@foreach($cart['taxBreakdownGrouped']['packaging'] ?? [] as $title => $amount)
    <tr>
        <td colspan="2">
            {{ $title }} {{ trans('lang.tax_on_packaging_fee') }}:
        </td>
        <td colspan="2" class="text-right">
            <span class="cart-price" data-price="{{ number_format($amount, $cart['decimal_degits'] ?? 2) }}">
                {{ number_format($amount, $cart['decimal_degits'] ?? 2) }}
            </span>
        </td>
    </tr>
@endforeach

{{-- Platform-level --}}
@foreach($cart['taxBreakdownGrouped']['platform'] ?? [] as $title => $amount)
    <tr>
        <td colspan="2">
            {{ $title }} {{ trans('lang.tax_on_platform_fee') }}:
        </td>
        <td colspan="2" class="text-right">
            <span class="cart-price" data-price="{{ number_format($amount, $cart['decimal_degits'] ?? 2) }}">
                {{ number_format($amount, $cart['decimal_degits'] ?? 2) }}
            </span>
        </td>
    </tr>
@endforeach

@endif

<tr>
    <td colspan="2"><strong>{{ trans('lang.total_tax') }}</strong></td>
    <td colspan="2" class="text-right">
        <strong>
            <span class="cart-price" data-price="{{ number_format($cart['totalTax'], $cart['decimal_degits'] ?? 2) }}">
                {{ number_format($cart['totalTax'], $cart['decimal_degits'] ?? 2) }}
            </span>
        </strong>
    </td>
</tr>

<!-- Grand Total -->
<tr>
    <td colspan="2"><strong>{{ trans('lang.total_amount') }}</strong></td>
    <td colspan="2" class="text-right">
        <strong>
            <span class="cart-price" data-price="{{ number_format($grandTotal, 2, '.', '') }}">
                {{ number_format($grandTotal, 2) }}
            </span>
        </strong>
    </td>
</tr>