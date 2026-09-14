@php
$user = Auth::user();
$role_has_permission = App\Models\Permission::where('role_id', $user->role_id)->pluck('permission')->toArray();
$service_type = @$_COOKIE['service_type'];
@endphp

<div class="sidebar-search">
    <input type="text" id="sideBarSearchInput" placeholder="{{trans('lang.search_menu')}}" autocomplete="one-time-code" onkeyup="filterMenu()">
</div>

<nav class="sidebar-nav">

    <ul id="sidebarnav">

        <li>
            <a class="waves-effect waves-dark" href="{{ route('dashboard') }}" aria-expanded="false">
                <i class="ri-home-4-fill"></i><span class="hide-menu">{{ trans('lang.dashboard') }}</span>
            </a>
        </li>

        @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
        @if(in_array('pos', $role_has_permission))
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.point_of_sale') }}</span></li>
        @endif
        @if(in_array('pos', $role_has_permission))
            <li>
                <a class="waves-effect waves-dark" href="{!! route('pos') !!}" aria-expanded="false">
                    <i class="mdi mdi-calculator"></i>
                    <span class="hide-menu">{{ trans('lang.point_of_sale') }}</span>
                </a>
            </li>
        @endif
        @if (in_array('pos', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('pos.order') !!}" aria-expanded="false">
                    <i class="mdi mdi-receipt"></i>
                    <span class="hide-menu">{{trans('lang.pos_orders')}}</span>
                </a>
            </li>
        @endif
        @endif

        @if($service_type == "delivery-service")
        @if (
        in_array('god-eye', $role_has_permission)
        )
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.live_monitoring') }}</span></li>
            <li><a class="waves-effect waves-dark" href="{!! route('map.multivendor') !!}" aria-expanded="false">
                    <i class="ri-home-wifi-fill"></i><span class="hide-menu">{{ trans('lang.live_tracking') }}</span>
                </a>
            </li>
        @endif
        @endif
        
        @if (
            in_array('roles', $role_has_permission) || 
            in_array('admins', $role_has_permission)
            )
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.access_management') }}</span></li>

            @if (in_array('roles', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('role.index') !!}" aria-expanded="false">
                    <i class="ri-map-pin-user-fill"></i>
                    <span class="hide-menu">{{ trans('lang.role_plural') }}</span>
                </a>
            </li>
            @endif

            @if (in_array('admins', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('admin.users') !!}" aria-expanded="false">
                    <i class="ri-user-2-fill"></i>
                    <span class="hide-menu">{{ trans('lang.admin_plural') }}</span>
                </a>
            </li>
            @endif

        @endif

        @if (
            in_array('vendors', $role_has_permission) || 
            in_array('approve_vendors', $role_has_permission) || 
            in_array('pending_vendors', $role_has_permission)
            )
            
            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.vendor_management') }}</span></li>

            @if (in_array('vendors', $role_has_permission) || in_array('approve_vendors', $role_has_permission) || in_array('pending_vendors', $role_has_permission))

                @if (in_array('vendors', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('vendors') !!}" aria-expanded="false">
                        <i class="ri-user-community-fill"></i>
                        <span class="hide-menu">{{ trans('lang.all_vendors') }}</span>
                    </a>
                </li>
                @endif

                @if (in_array('approve_vendors', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('vendors.approved') !!}" aria-expanded="false">
                        <i class="ri-user-star-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approved_vendors') }}</span>
                    </a>
                </li>
                @endif

                @if (in_array('pending_vendors', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('vendors.pending') !!}" aria-expanded="false">
                        <i class="ri-user-forbid-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approval_pending_vendors') }}</span>
                    </a>
                </li>
                @endif
                
            @endif
            @endif
        @endif

        @if (
            in_array('stores', $role_has_permission) ||
            in_array('drivers', $role_has_permission) || 
            in_array('approve_drivers', $role_has_permission) || 
            in_array('pending_drivers', $role_has_permission)
            )
            @if($service_type != "ondemand-service")
            <li class="nav-subtitle"><span class="nav-subtitle-span">
                @if($service_type == "delivery-service")
                    {{ trans('lang.store_and_driver_management') }}
                @elseif($service_type == "ecommerce-service")
                    {{ trans('lang.store_management') }}
                @elseif($service_type == "cab-service" || $service_type == "parcel_delivery" || $service_type == "rental-service")
                    {{ trans('lang.driver_management') }}
                @endif
            </span></li>
            @endif
        
            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('stores', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('stores') !!}" aria-expanded="false">
                        <i class="ri-shopping-bag-2-fill"></i>
                        <span class="hide-menu">{{ trans('lang.store_plural') }}</span>
                    </a>
                </li>
            @endif
            @if (in_array('employee', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('employee') !!}" aria-expanded="false">
                        <i class="mdi mdi-account"></i>
                        <span class="hide-menu">{{ trans('lang.employee_plural') }}</span>
                    </a>
                </li>
            @endif
            @endif
                
            @if($service_type != "ecommerce-service" && $service_type != "ondemand-service")
            @if (in_array('drivers', $role_has_permission) || in_array('approve_drivers', $role_has_permission) || in_array('pending_drivers', $role_has_permission))

                @if (in_array('drivers', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('drivers') !!}" aria-expanded="false">
                        <i class="ri-group-3-fill"></i>
                        <span class="hide-menu">{{ trans('lang.all_drivers') }}</span>
                    </a>
                </li>
                @endif

                @if (in_array('approve_drivers', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('drivers.approved') !!}" aria-expanded="false">
                        <i class="ri-user-follow-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approved_drivers') }}</span>
                    </a>
                </li>
                @endif

                @if (in_array('pending_drivers', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('drivers.pending') !!}" aria-expanded="false">
                        <i class="ri-user-unfollow-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approval_pending_drivers') }}</span>
                    </a>
                </li>
                @endif
               
            @endif
            @endif
        @endif

        @if($service_type == "cab-service" || $service_type == "parcel_delivery" || $service_type == "rental-service")
        @if (
            in_array('owners', $role_has_permission) || 
            in_array('approve_owners', $role_has_permission) || 
            in_array('pending_owners', $role_has_permission) || 
            in_array('fleet-drivers', $role_has_permission)
            )
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.owner_and_fleet_management') }}</span></li>
            @if(in_array('owners', $role_has_permission) || in_array('approve_owners', $role_has_permission) || in_array('pending_owners', $role_has_permission))

                @if (in_array('owners', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('owners') !!}" aria-expanded="false">
                        <i class="ri-account-box-2-fill"></i>
                        <span class="hide-menu">{{ trans('lang.all_owners') }}</span>
                    </a>
                </li>
                @endif
                @if (in_array('approve_owners', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('owners.approved') !!}" aria-expanded="false">
                        <i class="ri-account-pin-box-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approved_owners') }}</span>
                    </a>
                </li>
                @endif
                @if (in_array('pending_owners', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('owners.pending') !!}" aria-expanded="false">
                        <i class="ri-account-box-fill"></i>
                        <span class="hide-menu">{{ trans('lang.approval_pending_owners') }}</span>
                    </a>
                </li>
                @endif
                    
            @endif
            @if (in_array('fleet-drivers', $role_has_permission))
                <li>
                    <a class="waves-effect waves-dark" href="{!! route('fleet.drivers') !!}" aria-expanded="false">
                        <i class="ri-car-fill"></i>
                        <span class="hide-menu">{{ trans('lang.fleet_drivers') }}</span>
                    </a>
                </li>
            @endif
        @endif
        @endif

        @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
        @if (
            in_array('categories', $role_has_permission) || 
            in_array('items', $role_has_permission) || 
            in_array('item-attributes', $role_has_permission) || 
            in_array('review-attributes', $role_has_permission)
            )
            <li class="nav-subtitle">
                <span class="nav-subtitle-span">{{ trans('lang.category_and_items_management') }}</span>
            </li>
            @if (in_array('categories', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('categories') !!}" aria-expanded="false">
                    <i class="ri-article-fill"></i>
                    <span class="hide-menu">{{ trans('lang.category_plural') }}</span>
                </a>
            </li>
            @endif
            @if (in_array('items', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('items') !!}" aria-expanded="false">
                        <i class="ri-shopping-basket-fill"></i>
                        <span class="hide-menu">{{ trans('lang.item_plural') }}</span>
                    </a>
                </li>
            @endif
            @if (in_array('item-attributes', $role_has_permission) || in_array('review-attributes', $role_has_permission))
                @if (in_array('item-attributes', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('attributes') !!}" aria-expanded="false">
                        <i class="ri-archive-stack-fill"></i>
                        <span class="hide-menu">{{ trans('lang.item_attribute_plural') }}</span>
                    </a>
                </li>
                @endif
                @if (in_array('review-attributes', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('reviewattributes') !!}" aria-expanded="false">
                        <i class="ri-shield-star-fill"></i>
                        <span class="hide-menu">{{ trans('lang.review_attribute_plural') }}</span>
                    </a>
                </li>
                @endif
                @if (in_array('bulk_import_products', $role_has_permission))
                    <li><a class="waves-effect waves-dark" href="{!! route('bulk_import_products') !!}" aria-expanded="false">
                            <i class="mdi mdi-import"></i>
                            <span class="hide-menu">{{ trans('lang.bulk_import_products_plural') }}</span>
                        </a>
                    </li>
                @endif
            @endif
        @endif
        @endif

        @if($service_type == "ecommerce-service" || $service_type == "cab-service")
        @if (
        in_array('brands', $role_has_permission) ||
        in_array('destinations', $role_has_permission)
        )
            <li class="nav-subtitle"><span class="nav-subtitle-span">
                @if($service_type == "ecommerce-service")
                    {{ trans('lang.brand_management') }}
                @elseif($service_type == "cab-service")
                    {{ trans('lang.destination_management') }}
                @endif
            </span></li>
            
            @if($service_type == "ecommerce-service")
            @if (in_array('brands', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('brands') !!}" aria-expanded="false">
                    <i class="ri-registered-fill"></i>
                    <span class="hide-menu">{{ trans('lang.brand') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "cab-service")
            @if (in_array('destinations', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('destinations') !!}" aria-expanded="false">
                    <i class="ri-map-pin-fill"></i>
                    <span class="hide-menu">{{ trans('lang.destination') }}</span>
                </a>
            </li>
            @endif
            @endif
        @endif
        @endif
        
        @if (in_array('report', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('/report/sales') !!}" aria-expanded="false">
                <i class="mdi mdi-calendar-check"></i>
                <span class="hide-menu">{{ trans('lang.reports_sale') }}</span>
            </a>
        </li>
        <li><a class="waves-effect waves-dark" href="{!! url('/report/tax') !!}" aria-expanded="false">
                <i class="mdi mdi-calendar-clock"></i>
                <span class="hide-menu">{{ trans('lang.reports_tax') }}</span>
            </a>
        </li>
        @endif

        @php
        $blockedServices = ['cab-service', 'parcel_delivery', 'rental-service'];
        @endphp
        @if(!in_array($service_type, $blockedServices))
        @if (in_array('subscription-plans', $role_has_permission) || in_array('subscription-history', $role_has_permission))
        <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.business_setup') }}</span></li>

        <li><a class="waves-effect waves-dark" href="{!! route('subscription-plans.index') !!}" aria-expanded="false">
                <i class="ri-calendar-todo-fill"></i>
                <span class="hide-menu">{{ trans('lang.subscription_plans') }}</span>
            </a>
        </li>
        <li><a class="waves-effect waves-dark" href="{!! route('subscription.subscriptionPlanHistory') !!}" aria-expanded="false">
                <i class="ri-chat-history-fill"></i>
                <span class="hide-menu">{{ trans('lang.subscription_history') }}</span>
            </a>
        </li>
        @endif
        @endif

        @if (
        in_array('orders', $role_has_permission) ||
        in_array('deliveryman', $role_has_permission) ||
        in_array('gift-cards', $role_has_permission) ||
        in_array('coupons', $role_has_permission) ||
        in_array('advertisements', $role_has_permission) || 
        in_array('documents', $role_has_permission)
        )
            @if($service_type != "ondemand-service")
            <li class="nav-subtitle">
                <span class="nav-subtitle-span">
                    @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
                        {{ trans('lang.order_and_promotions_management') }}
                    @else
                        {{ trans('lang.document_management') }}
                    @endif
                </span>
            </li>
            @endif
        
            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('orders', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('orders') !!}" aria-expanded="false">
                    <i class="ri-shopping-bag-fill"></i>
                    <span class="hide-menu">{{ trans('lang.order_plural') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "delivery-service")
            @if (in_array('deliveryman', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('deliveryman') !!}" aria-expanded="false">
                    <i class="ri-riding-fill"></i>
                    <span class="hide-menu">{{ trans('lang.deliveryman') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('gift-cards', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('gift-card.index') !!}" aria-expanded="false">
                    <i class="ri-gift-fill"></i>
                    <span class="hide-menu">{{ trans('lang.gift_card_plural') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('coupons', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('coupons') !!}" aria-expanded="false">
                    <i class="ri-coupon-fill"></i>
                    <span class="hide-menu">{{ trans('lang.coupon_plural') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('advertisements', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('advertisements') !!}" aria-expanded="false">
                    <i class="ri-file-list-3-fill"></i>
                    <span class="hide-menu">{{ trans('lang.add_list') }}</span>
                </a>
            </li>
            <li><a class="waves-effect waves-dark" href="{!! route('advertisements.request') !!}" aria-expanded="false">
                    <i class="ri-file-ai-2-fill"></i>
                    <span class="hide-menu">{{ trans('lang.add_requests') }}</span>
                </a>
            </li>
            @endif
            @endif
            
            @if($service_type != "ondemand-service")
            @if (in_array('documents', $role_has_permission))
                <li><a class="waves-effect waves-dark" href="{!! route('documents') !!}" aria-expanded="false">
                        <i class="ri-file-pdf-fill"></i>
                        <span class="hide-menu">{{ trans('lang.document_plural') }}</span>
                    </a>
                </li>
            @endif
            @endif
        @endif

        @if (in_array('general-notifications', $role_has_permission) || in_array('dynamic-notifications', $role_has_permission))
        <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.notification_management') }}</span></li>
        @if (in_array('general-notifications', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('notification') !!}" aria-expanded="false">
                <i class="ri-notification-fill"></i>
                <span class="hide-menu">{{ trans('lang.send_notification') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('dynamic-notifications', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('dynamic-notification.index') !!}" aria-expanded="false">
                <i class="ri-notification-snooze-fill"></i>
                <span class="hide-menu">{{ trans('lang.dynamic_notification') }}</span>
            </a>
        </li>
        @endif
        @endif
        @if (in_array('supportHistory', $role_has_permission) )
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.help_support') }}</span></li>
            <li>
                <a class="waves-effect waves-dark" href="{{ route('users.support') }}" aria-expanded="false">
                    <i class="mdi mdi-message-alert"></i>
                    <span class="hide-menu">{{ trans('lang.help_support') }}</span>
                </a>
            </li>
            
        @endif
        
        @if (
            (
            in_array('providers', $role_has_permission) ||
            in_array('ondemand-categories', $role_has_permission) ||
            in_array('ondemand-coupons', $role_has_permission) ||
            in_array('ondemand-services', $role_has_permission) ||
            in_array('ondemand-workers', $role_has_permission) ||
            in_array('ondemand-bookings', $role_has_permission) ||
            in_array('parcel-service-god-eye', $role_has_permission) ||
            in_array('parcel-categories', $role_has_permission) ||
            in_array('parcel-weight', $role_has_permission) ||
            in_array('parcel-coupons', $role_has_permission) ||
            in_array('parcel-orders', $role_has_permission) ||
            in_array('cab-service-god-eye', $role_has_permission) ||
            in_array('rides', $role_has_permission) ||
            in_array('sos-rides', $role_has_permission) ||
            in_array('cab-promo', $role_has_permission) ||
            in_array('complaints', $role_has_permission) ||
            in_array('cab-vehicle-type', $role_has_permission) ||
            in_array('make', $role_has_permission) ||
            in_array('model', $role_has_permission) ||
            in_array('rental-plural-god-eye', $role_has_permission) ||
            in_array('rental-vehicle-type', $role_has_permission) ||
            in_array('rental-discount', $role_has_permission) ||
            in_array('rental-orders', $role_has_permission) ||
            in_array('rental-vehicle', $role_has_permission) ||
            in_array('rental-package', $role_has_permission)
            ) 
            && 
            $service_type != "delivery-service" && $service_type != "ecommerce-service"
        )
        <li class="nav-subtitle">
            <span class="nav-subtitle-span">
                @if($service_type == "ondemand-service")
                    {{ trans('lang.ondemand_services_management') }}
                @elseif($service_type == "cab-service")
                    {{ trans('lang.cab_services_management') }}
                @elseif($service_type == "parcel_delivery")
                    {{ trans('lang.parcel_services_management') }}
                @elseif($service_type == "rental-service")
                    {{ trans('lang.rental_services_management') }}
                @endif
            </span>
        </li>
        @endif

        @if($service_type == "ondemand-service")
        @if (in_array('providers', $role_has_permission) || 
            in_array('ondemand-categories', $role_has_permission) || 
            in_array('ondemand-coupons', $role_has_permission) || 
            in_array('ondemand-services', $role_has_permission) || 
            in_array('ondemand-workers', $role_has_permission) || 
            in_array('ondemand-bookings', $role_has_permission)
        )

        @if (in_array('providers', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('providers') !!}" aria-expanded="false">
                <i class="ri-info-card-fill"></i>
                <span class="hide-menu">{{ trans('lang.provider_plural') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('ondemand-categories', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('/ondemand-categories') !!}" aria-expanded="false">
                <i class="ri-todo-fill"></i>
                <span class="hide-menu">{{ trans('lang.category') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('ondemand-coupons', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('ondemand-coupons') !!}" aria-expanded="false">
                <i class="ri-coupon-4-fill"></i>
                <span class="hide-menu">{{ trans('lang.coupon_plural') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('ondemand-services', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('ondemand-services') !!}" aria-expanded="false">
                <i class="ri-barcode-box-fill"></i>
                <span class="hide-menu">{{ trans('lang.service_plural') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('ondemand-workers', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('ondemand-workers') !!}" aria-expanded="false">
                <i class="ri-id-card-fill"></i>
                <span class="hide-menu">{{ trans('lang.worker_plural') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('ondemand-bookings', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! url('ondemand-bookings') !!}" aria-expanded="false">
                <i class="ri-bookmark-3-fill"></i>
                <span class="hide-menu">{{ trans('lang.booking_plural') }}</span>
            </a>
        </li>
        @endif
       
        @endif
        @endif

        @if($service_type == "parcel_delivery")
        @if (
            in_array('parcel-service-god-eye', $role_has_permission) || 
            in_array('parcel-categories', $role_has_permission) || 
            in_array('parcel-weight', $role_has_permission) || 
            in_array('parcel-coupons', $role_has_permission) || 
            in_array('parcel-orders', $role_has_permission)
        )

        @if (in_array('parcel-service-god-eye', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('map.parcel') !!}" aria-expanded="false">
                <i class="ri-taxi-wifi-fill"></i>
                <span class="hide-menu">{{ trans('lang.live_tracking') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('parcel-categories', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('parcelCategory') !!}" aria-expanded="false">
                <i class="ri-box-3-fill"></i>
                <span class="hide-menu">{{ trans('lang.parcel_category') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('parcel-weight', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('parcel_weight') !!}" aria-expanded="false">
                <i class="ri-weight-fill"></i>
                <span class="hide-menu">{{ trans('lang.parcel_weight') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('parcel-coupons', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('parcel_coupons') !!}" aria-expanded="false">
                <i class="ri-ticket-2-fill"></i>
                <span class="hide-menu">{{ trans('lang.parcel_coupons') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('parcel-orders', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('parcel_orders') !!}" aria-expanded="false">
                <i class="ri-shopping-cart-fill"></i>
                <span class="hide-menu">{{ trans('lang.parcel_orders') }}</span>
            </a>
        </li>
        @endif

        @endif
        @endif

        @if($service_type == "cab-service")
        @if (
        in_array('cab-service-god-eye', $role_has_permission) || 
        in_array('rides', $role_has_permission) || 
        in_array('sos-rides', $role_has_permission) || 
        in_array('cab-promo', $role_has_permission) || 
        in_array('complaints', $role_has_permission) || 
        in_array('cab-vehicle-type', $role_has_permission) ||
        in_array('make', $role_has_permission) ||
        in_array('model', $role_has_permission)
        )

        @if (in_array('cab-service-god-eye', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('map.cab') !!}" aria-expanded="false">
                <i class="ri-train-wifi-fill"></i>
                <span class="hide-menu">{{ trans('lang.live_tracking') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('rides', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rides') !!}" aria-expanded="false">
                <i class="ri-police-car-fill"></i>
                <span class="hide-menu">{{ trans('lang.rides') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('sos-rides', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('sos') !!}" aria-expanded="false">
                <i class="ri-car-washing-fill"></i>
                <span class="hide-menu">{{ trans('lang.sos_ride') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('cab-promo', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('settings.promos') !!}" aria-expanded="false">
                <i class="ri-discount-percent-fill"></i>
                <span class="hide-menu">{{ trans('lang.promo_pural') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('complaints', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('complaints') !!}" aria-expanded="false">
                <i class="ri-sticky-note-add-fill"></i>
                <span class="hide-menu">{{ trans('lang.complaints') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('cab-vehicle-type', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('vehicleType') !!}" aria-expanded="false">
                <i class="ri-caravan-fill"></i>
                <span class="hide-menu">{{ trans('lang.vehicle_type') }}</span>
            </a>
        </li>
        @endif

        @endif
        @endif

        @if($service_type == "cab-service" || $service_type == "rental-service")
        @if (in_array('make', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('carMake') !!}" aria-expanded="false">
                <i class="ri-car-fill"></i>
                <span class="hide-menu">{{ trans('lang.make') }}</span>
            </a>
        </li>
        @endif

        @if (in_array('model', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('carModel') !!}" aria-expanded="false">
                <i class="ri-roadster-fill"></i>
                <span class="hide-menu">{{ trans('lang.model') }}</span>
            </a>
        </li>
        @endif
        @endif

        @if($service_type == "rental-service")
        @if (
            in_array('rental-plural-god-eye', $role_has_permission) || 
            in_array('rental-vehicle-type', $role_has_permission) || 
            in_array('rental-discount', $role_has_permission) || 
            in_array('rental-orders', $role_has_permission) || 
            in_array('rental-vehicle', $role_has_permission) || 
            in_array('rental-package', $role_has_permission)
        )

        @if (in_array('rental-plural-god-eye', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('map.rental') !!}" aria-expanded="false">
                <i class="ri-router-fill"></i>
                <span class="hide-menu">{{ trans('lang.live_tracking') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('rental-vehicle-type', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rentalvehicleType') !!}" aria-expanded="false">
                <i class="ri-police-car-fill"></i>
                <span class="hide-menu">{{ trans('lang.rental_vehicle_type') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('rental-discount', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rentaldiscount') !!}" aria-expanded="false">
                <i class="ri-discount-percent-fill"></i>
                <span class="hide-menu">{{ trans('lang.rental_discount') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('rental-orders', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rental_orders') !!}" aria-expanded="false">
                <i class="ri-shopping-cart-2-fill"></i>
                <span class="hide-menu">{{ trans('lang.rental_orders') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('rental-vehicle', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rentalvehicle') !!}" aria-expanded="false">
                <i class="ri-police-car-fill"></i>
                <span class="hide-menu">{{ trans('lang.rental_vehicle') }}</span>
            </a>
        </li>
        @endif
        @if (in_array('rental-package', $role_has_permission))
        <li><a class="waves-effect waves-dark" href="{!! route('rental-package') !!}" aria-expanded="false">
                <i class="ri-police-car-fill"></i>
                <span class="hide-menu">{{ trans('lang.rental_packages') }}</span>
            </a>
        </li>
        @endif
        
        @endif
        @endif

        @if (
        in_array('payout-request-vendor', $role_has_permission) ||
        in_array('drivers-payout', $role_has_permission) ||
        in_array('payout-request-provider', $role_has_permission)
        )
            <li class="nav-subtitle"><span class="nav-subtitle-span">{{ trans('lang.disbursement_management') }}</span></li>

            @if($service_type == "delivery-service" || $service_type == "ecommerce-service")
            @if (in_array('payout-request-vendor', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('payoutRequests.vendor.disbursement') !!}" aria-expanded="false">
                    <i class="ri-store-fill"></i>
                    <span class="hide-menu">{{ trans('lang.store_disburesement') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "cab-service" || $service_type == "parcel_delivery" || $service_type == "rental-service")
            @if (in_array('payout-request-vendor', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('payoutRequests.owner.disbursement') !!}" aria-expanded="false">
                    <i class="ri-store-fill"></i>
                    <span class="hide-menu">{{ trans('lang.owner_disburesement') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type != "ecommerce-service" && $service_type != "ondemand-service")
            @if (in_array('drivers-payout', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('payoutRequests.driver.disbursement') !!}" aria-expanded="false">
                    <i class="ri-store-2-fill"></i>
                    <span class="hide-menu">{{ trans('lang.driver_disburesement') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($service_type == "ondemand-service")
            @if (in_array('payout-request-provider', $role_has_permission))
            <li><a class="waves-effect waves-dark" href="{!! route('payoutRequests.providers.disbursement') !!}" aria-expanded="false">
                    <i class="ri-store-3-fill"></i>
                    <span class="hide-menu">{{ trans('lang.provider_disburesement') }}</span>
                </a>
            </li>
            @endif
            @endif
            
        @endif
        
    </ul>

    <p class="web_version"></p>

</nav>
<script>
    function filterMenu() {
        const searchInput = document.getElementById('sideBarSearchInput').value.toLowerCase();
        const menuItems = document.getElementById('sidebarnav').getElementsByTagName('li');
        for (let i = 0; i < menuItems.length; i++) {
            const item = menuItems[i];
            const itemText = item.textContent.toLowerCase();
            if (itemText.indexOf(searchInput) === -1) {
                item.style.display = 'none';
            } else {
                item.style.display = '';
            }
        }
    }
</script>