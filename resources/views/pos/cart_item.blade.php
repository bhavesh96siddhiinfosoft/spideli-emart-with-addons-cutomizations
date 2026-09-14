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
                            @if(!empty($item['variant_info']) && is_array($item['variant_info']) && !empty($item['variant_info']['variant_options']) && is_array($item['variant_info']['variant_options']))
                                <small class="text-muted">
                                    {{ implode(', ', $item['variant_info']['variant_options']) }}
                                </small>
                            @endif


                            {{-- Add-ons --}}
                            @if(!empty($item['extras']))
                                <small class="text-muted d-block mt-1">
                                    <strong>{{ trans('lang.addons_title') }}:</strong>
                                </small>

                                @foreach($item['extras'] as $extra)
                                    <small class="text-muted d-block">
                                        • {{ $extra }}
                                    </small>
                                @endforeach

                                @if(($item['extras_price'] ?? 0) > 0)
                                    <small class="text-success d-block">
                                        + 
                                        <span class="cart-price"
                                            data-price="{{ number_format($item['extras_price'], $item['decimal_degits'] ?? 2, '.', '') }}">
                                            {{ number_format($item['extras_price'], $item['decimal_degits'] ?? 2) }}
                                        </span>
                                    </small>
                                @endif
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