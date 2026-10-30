@php
    $cart = session('cart', []);
@endphp

@if(!empty($cart['item']))

    @foreach($cart['item'] as $vendorId => $vendorItems)

        @foreach($vendorItems as $index => $item)
            <tr>
                <td class="order-product">
                    <div class="d-flex align-items-center"> 
                        <div class="order-product-box">
                            <img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="{{ $item['image'] }}" alt="image">
                        </div>
                        <div class="orders-tracking">
                            <h6 class="text-dark">{{ $item['name'] }}</h6>
                            @if(!empty($item['is_wholesale']))
                                <span class="badge badge-info">{{ trans('lang.wholesale') }}</span>
                                @if(!empty($item['wholesale_applied_min_qty']))
                                    {{-- WHICH tier is running, not just that one is.
                                         With a ladder of 10 / 25 / 50 the counter
                                         cannot otherwise tell. --}}
                                    <span class="badge badge-light">{{ str_replace(':count', $item['wholesale_applied_min_qty'], trans('lang.wholesale_from_units')) }}</span>
                                @endif
                            @endif
                            @php
                                /* Sold in packs. Shown because the minus button
                                   stopping at ten is otherwise indistinguishable
                                   from a stuck cart. */
                                $packMinimum = 0;
                                if (($item['sale_type'] ?? 'both') === 'wholesale') {
                                    $packTiers = $item['wholesale_tiers'] ?? [];
                                    $packMinimum = !empty($packTiers)
                                        ? (int) $packTiers[0]['minQty']
                                        : (int) ($item['wholesale_min_qty'] ?? 0);
                                }
                            @endphp
                            @if($packMinimum > 1)
                                <span class="badge badge-dark">{{ str_replace(':count', $packMinimum, trans('lang.wholesale_only_minimum')) }}</span>
                            @endif
                            @if(!empty($item['variant_info']) && is_array($item['variant_info']) && !empty($item['variant_info']['variant_options']) && is_array($item['variant_info']['variant_options']))
                                <small class="text-muted">
                                    {{ implode(', ', $item['variant_info']['variant_options']) }}
                                </small>
                            @endif


                            {{-- Point 68: each add-on at its own quantity, with its own +/-
                                 (0 removes that add-on, never the line). A legacy line
                                 shows its add-ons at the product's quantity. --}}
                            @php
                                $lineAddons = (isset($item['addons']) && is_array($item['addons'])) ? $item['addons'] : null;
                                $legacyNames = $lineAddons === null ? array_values(array_filter(array_map(function ($n) { return trim(str_replace('"', '', (string) $n)); }, (array) ($item['extras'] ?? [])), 'strlen')) : [];
                                $addonsTotal = $lineAddons !== null
                                    ? array_sum(array_map(function ($a) { return (float) ($a['price'] ?? 0) * (int) ($a['quantity'] ?? 0); }, $lineAddons))
                                    : (float) ($item['extras_price'] ?? 0) * (int) ($item['quantity'] ?? 0);
                                $digits = $item['decimal_degits'] ?? 2;
                            @endphp
                            @if(!empty($lineAddons) || !empty($legacyNames))
                                <div class="line-addons">
                                    <div class="line-addons-head">
                                        <span>{{ trans('lang.addons_title') }}</span>
                                        <span class="cart-price" data-price="{{ number_format($addonsTotal, $digits, '.', '') }}">{{ number_format($addonsTotal, $digits) }}</span>
                                    </div>
                                    @if(!empty($lineAddons))
                                        @foreach($lineAddons as $addon)
                                            <div class="line-addon">
                                                <span class="line-addon-qty">&times;{{ (int) $addon['quantity'] }}</span>
                                                <span class="line-addon-name">{{ $addon['name'] }}
                                                    <small>{{ (int) $addon['quantity'] }} &times; <span class="cart-price" data-price="{{ number_format((float) $addon['price'], $digits, '.', '') }}">{{ number_format((float) $addon['price'], $digits) }}</span></small>
                                                </span>
                                                <span class="line-addon-sub text-nowrap">
                                                    <button type="button" class="btn btn-xs btn-secondary pos-line-addon-step" data-step="-1" data-index="{{ $index }}" data-name="{{ $addon['name'] }}" data-qty="{{ (int) $addon['quantity'] }}">-</button>
                                                    <span class="cart-price" data-price="{{ number_format((float) $addon['price'] * (int) $addon['quantity'], $digits, '.', '') }}">{{ number_format((float) $addon['price'] * (int) $addon['quantity'], $digits) }}</span>
                                                    <button type="button" class="btn btn-xs btn-secondary pos-line-addon-step" data-step="1" data-index="{{ $index }}" data-name="{{ $addon['name'] }}" data-qty="{{ (int) $addon['quantity'] }}">+</button>
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach($legacyNames as $legacyName)
                                            <div class="line-addon">
                                                <span class="line-addon-qty">&times;{{ (int) ($item['quantity'] ?? 0) }}</span>
                                                <span class="line-addon-name">{{ $legacyName }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            @if(!empty($item['taxLabel']))
                            <br>
                            <small class="text-muted">
                                {{ trans('lang.tax') }}: {{ $item['taxLabel'] }}
                            </small>
                            @endif
                        </div>
                    </div>
                </td>
            
                @php
                    $variantPrice = $item['variant_info']['variant_price'] ?? 0;
                    $itemPrice = ($variantPrice > 0) ? $variantPrice : ($item['original_base_price'] ?? 0);

                    /* A wholesale line is charged at the repriced unit price, so
                       it must be shown at that price too - variant lines included,
                       which otherwise display their own variant_price. */
                    if (!empty($item['is_wholesale'])) {
                        $itemPrice = $item['original_base_price'] ?? $itemPrice;
                    }
                @endphp

                <td class="text-green">
                    <span class="cart-price" data-price="{{ number_format($itemPrice, $item['decimal_degits'] ?? 2) }}">
                        {{ number_format($itemPrice, $item['decimal_degits'] ?? 2) }}
                    </span>
                </td>

                <td>
                    <div class="input-group-prepend">
                        <input type="hidden" class="product-variant" name="variant_ids[]" value="{{ $item['variant_info']['variant_id'] ?? '' }}">
                        <button type="button" class="cart-quantity-input btn btn-xs btn-secondary update-cart" data-operation="plus" data-index="{{ $index }}">+</button>
                        <input class="cart-quantity-input-new form-control text-center p-0" name="quantity[]" value="{{ $item['quantity'] }}" readonly>
                        <button type="button" class="cart-quantity-input btn btn-xs btn-secondary update-cart" data-operation="minus" data-index="{{ $index }}">-</button>
                    </div>
                </td>
                <td class="delete-btn text-center">
                    
                    <button type="button" class="btn btn-danger btn-rounded delete-cart-item" data-index="{{ $index }}">
                        <i class="fa fa-trash"></i>
                    </button>

                </td>
            </tr>
       @endforeach
    @endforeach
@endif