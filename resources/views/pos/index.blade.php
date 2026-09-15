@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor vendorTitle">{{trans('lang.point_of_sale')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.point_of_sale')}}</li>
            </ol>
        </div>
    <div>
</div>
</div>

<div class="container-fluid">
    <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
        {{trans('lang.processing')}}
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card pos-card">
                <div class="card-header">
                    <div class="pos-search d-flex align-items-center gap-10">
                        <label class="mb-0">{{trans('lang.all_products')}}</label>
                        <div class="form-group mb-0 custom-dropdown" id="product_vendor">
                            <select class="form-control search_category" id="vendor_dropdown" name="vendor_dropdown" tabindex="-1" aria-hidden="true" data-select2-id="product_vendor">
                                <option value="" data-select2-id="23">{{trans('lang.select_vendor')}}</option>                                                            
                            </select>
                        </div>
                        <div class="form-group mb-0 custom-dropdown" id="product_categories">
                            <select class="form-control search_category" id="category_dropdown" name="category_dropdown" tabindex="-1" aria-hidden="true" data-select2-id="product_categories">
                                <option value="" data-select2-id="23">{{trans('lang.select_categories')}}</option>                                                            
                            </select>
                        </div>
                        <div class="form-group mb-0" id="search-box">
                            <input type="text" name="search-product" id="search-product" class="form-control" placeholder="{{trans('lang.search_products')}}">
                        </div>
                        
                        <input type="button" class="btn btn-xs btn-secondary ml-2" id="clear_filter" value="{{trans('lang.clear_filter')}}">
                    </div>
                </div>
                <div class="card-body">

                    <div class="pos-contant-wrap">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="shop-card">
                                    <div class="row d-flex" id="product-list">
                                        
                                    </div>
                                    <div class="pagination-container mt-5">
                                        <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-center">
                                               <nav aria-label="Page navigation example">
                                                    <ul class="pagination" id="pagination-numbers">
                                                       
                                                    </ul>
                                               </nav>
                                            </div>
                                          </div>
                                        </div>
                                   </div>
                                </div>
                            </div>


                            <div class="col-md-5">
                                <div class="pos-right border-left pl-4">                                  
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <p class="text text-dark mb-0 font-weight-bold">
                                            {{trans('lang.select_user')}}
                                        </p>

                                        <a href="{{route('users.create')}}" class="btn btn-sm btn-primary rounded">
                                            {{trans('lang.user_create')}}
                                        </a>
                                    </div>

                                    <div class="mt-2 d-flex">
                                        <select class="select_user form-control" id="user_dropdown"  name="user_dropdown" data-select2-id="select_user_id" tabindex="-1" aria-hidden="true">
                                            <option value="">{{trans('lang.search_for_user')}}</option>
                                        </select>
                                        <input type="button" class="btn btn-xs btn-secondary ml-2" id="clear_user_search" value="{{trans('lang.clear_filters')}}">
                                    </div>     
                                    <!-- User Details Block -->
                                    <div id="user_detail_block" class="mt-3 p-3 border rounded d-none bg-light">
                                        <div class="mb-1">
                                            <strong>{{trans('lang.name')}} :</strong>
                                            <span id="user_detail_name">-</span>
                                        </div>

                                        <div class="mb-1">
                                            <strong>{{trans('lang.user_email')}} :</strong>
                                            <span id="user_detail_email">-</span>
                                        </div>

                                        <div class="mb-1">
                                            <strong>{{trans('lang.user_phone')}} :</strong>
                                            <span id="user_detail_phone">-</span>
                                        </div>

                                        <div>
                                            <strong>{{trans('lang.wallet_Balance')}} :</strong>
                                            <span class="text-primary" id="user_detail_wallet">$0.00</span>
                                        </div>
                                    </div>
                               
                                    <div class="shop-cart-table">
                                        <h3 class="mb-2 text-dark font-weight-semibold">{{trans('lang.cart')}}</h3>
                                        <div class="cart-table border-bottom pb-2 mb-2">
                                            <table cellpadding="0" cellspacing="0" class="table table-striped table-valign-middle" id="cartTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('lang.item_plural')}}</th>
                                                        <th>{{trans('lang.item_price')}}</th>
                                                        <th>{{trans('lang.qty')}}</th>
                                                        <th>{{trans('lang.edit')}}</th>
                                                    </tr>
                                                </thead>
                                                
                                                <tbody id="cart_list"> 
                                                    @if(empty(session('cart')))
                                                    <tr>
                                                        <td colspan="4" class="text-center">{{trans('lang.cart_is_empty')}}</td>
                                                    </tr>
                                                    @endif
                                                    @include('pos.cart_item')
                                                </tbody>

                                                <tfoot id="cart_total">
                                                    @if(!empty(session('cart')))
                                                        @include('pos.cart_total')
                                                    @endif
                                                </tfoot>
                                            
                                            </table>
                                        </div>

                                        <div class="shop-payment-method" style="display: none;">
                                            <h3 class="mb-2 text-dark font-weight-semibold pb-2 border-bottom">{{trans('lang.payment_methods')}}</h3>
                                            <div class="payment-method-list">
                                                <div class="form-radio width-100 pb-2 border-bottom mb-2">
                                                    <input class="raio-input" name="paymentmethod" id="cash" type="radio" value="cod" checked>
                                                    <label class="control-label" for="cash">{{trans('lang.cash')}}</label>
                                                </div> 
                                                <div class="form-radio width-100 pb-2 border-bottom mb-2">
                                                    <input class="raio-input" name="paymentmethod" id="card" type="radio" value="Card">
                                                    <label class="control-label" for="card">{{trans('lang.card')}}</label>
                                                </div>                                              
                                            </div>                                            
                                        </div>

                                        <div class="text-center mt-4 shop-payment-method" style="display: none;">
                                           <button class="btn btn-sm btn-clear_cart btn-danger mb-2 mx-3" id="clear_cart_btn">{{trans('lang.clear_cart')}}</button>
                                           <button class="btn btn-sm btn-purchase btn-primary mb-2" id="place_order_btn">{{trans('lang.place_order')}}</button>
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="addToCartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('lang.add_to_cart') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="product-details mb-3">
                    <div class="d-flex align-items-center d-flex align-items-center box border">
                       <div class="mr-3 model-cart-img"> 
                        <img id="modal-product-image" src="" style="width:60px;height:60px;" />
                       </div>
                        <div class="model-cart-detail">
                            <h5 id="modal-product-name" class="mb-1 text-dark"></h5>
                            <p id="modal-product-price" class="mb-0 text-dark"></p>
                        </div>
                      
                    </div>
                </div>
                <div class="product-details mb-3">
                    <div class="align-items-center">                       
                        <div class="model-cart-detlist">
                            <div id="modal-product-quantity" class="pb-2 mb-2 d-flex align-items-center justify-content-between border-bottom"><strong class="text-dark">{{trans('lang.quantity')}} : </strong><span class="modal-product-quantity-count"></span></div>                                                    
                            <div id="modal-product-vendor" class="pb-2 mb-2 d-flex align-items-center justify-content-between border-bottom"><strong class="text-dark">{{trans('lang.vendor')}} :</strong><span class="modal-product-vendor-name"></span></div>                           
                           
                            <div class="product-attr-detail">
                                <div id="variation_info_temp">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <strong class="text-dark">{{ trans('lang.price') }}:</strong><span id="variant_price"></span>
                                        
                                    </div>
                                    <span id="variant_qty" class="text-muted"></span>
                                </div>
                                <div id="modal_wholesale_line" style="display:none;">
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <strong class="text-dark">{{ trans('lang.wholesale') }}:</strong>
                                        <span id="modal_wholesale_value" class="text-success"></span>
                                    </div>
                                </div>
                                <div class="attribute_price_div d-none">
                                    <span class="price">
                                        <div class="variation_info" id="modal-variation-info">
                                            <span id="modal-variant-price"></span>
                                            <span id="modal-variant-qty"></span>
                                        </div>
                                    </span>
                                    <input type="hidden" id="selected_variant_id" name="selected_variant_id">
                                    <input type="hidden" id="selected_variant_price" name="selected_variant_price">
                                </div>
                            </div>

                           
                        </div>
                    </div>
                </div>
                <div id="modal-attributes-container" class="mb-3 row"></div>
                <div id="modal-addons-container" class="mb-3"></div>

                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="modal-add-cart-btn">{{ trans('lang.add_to_cart') }}</a>
                    </button>                    
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    var section_id = getCookie('section_id') || '';

    // ========== GLOBAL CACHES ==========
    var productCache = new Map();
    var categoryCache = new Map();
    var vendorCache = new Map();
    var userCache = new Map();
    var attributeCache = new Map();
    
    // ========== CONFIGURATION ==========
    var config = {
        pageSize: 12,
        decimal_degits: 2,
        currentCurrency: '$',
        currencyAtRight: false,
        placeholderImage: '/images/default-placeholder.png',
        isCommissionEnabled: false,
        commissionType: 'percentage',
        commissionValue: 0
    };
    
    // ========== FIREBASE REFERENCES ==========
    var database = firebase.firestore();
    var refProducts = database.collection('vendor_products').where('section_id', '==', section_id).where('publish', '==', true);
    var refCategories = database.collection('vendor_categories').where('section_id', '==', section_id);
    var refVendor = database.collection('vendors').where('section_id', '==', section_id);
    var refUsers = database.collection('users');
    
    // ========== STATE MANAGEMENT ==========
    var state = {
        currentPage: 1,
        totalPages: 1,
        selectedCategoryId: null,
        selectedVendorId: null,
        searchText: "",
        confirmedVendorId: null,
        isProcessing: false,
        pageCursors: {},
        deliveryChargemain: { amount: 0 }
    };
    
    var currencyData = '';
    let taxesByScope = {};
    var taxScope = '';
    var platformCharge = '0';
    
    var orderBasePrice = 0;
    var orderTaxAmount = 0;
    var total_tax_amount = 0;
    var subscriptionTotalOrders = -1;
    let isEcommerceService = false;   // default
    
    var packagingCharge = '0';
    var packagingChargeEnable = false;
    
    var refGlobal = database.collection('settings').doc("globalSettings");
    refGlobal.get().then(async function(
        settingSnapshots) {
        if (settingSnapshots.data()) {
            var settingData = settingSnapshots.data();
            taxScope = settingData.taxScope;
        }
    });

    // Hide/show payment methods section based on cart content
    function updatePaymentMethodVisibility() {
        const cartHasItems = $('#cart_list tr').length > 0 && 
                            !$('#cart_list').find('td[colspan="4"]').length; 
        
        if (cartHasItems) {
            $('.shop-payment-method').show();
        } else {
            $('.shop-payment-method').hide();
        }
    }
    
    // ========== INITIALIZATION ==========
    $(document).ready(async function () {
        // Show loading indicator
        $('#data-table_processing').show();
        await database.collection('sections').doc(section_id).get().then(snapshot => {
            var vendor_data = snapshot.data();
            packagingChargeEnable = vendor_data.packagingChargeEnable;
        });
        
        // Clear cart if has items
        const cartHasItems = $('#cartTable tbody tr').not(':has(td[colspan])').length > 0;
        if(cartHasItems){
            await clearCartAjax();
        }
        
        // Load initial data in parallel with error handling
        try {
            await Promise.all([
                loadEssentialSettings(),
                loadVendor(),
                loadCategories(),
                loadUsers(),
                loadSectionServiceType()
            ]);
            
            // Then load products
            await loadProducts();
            
            // Initialize UI
            formatCartPrices();
            initializeSelect2();
            
        } catch (error) {
            console.error('Initialization error:', error);            
        } finally {
            $('#data-table_processing').hide();
        }
        
        // Event listeners
        setupEventListeners();
        
        // Add event listeners for cart operations (must be after DOM is ready)
        $(document).on('click', '.update-cart', handleUpdateCart);
        $(document).on('click', '.delete-cart-item', handleDeleteCartItem);
        $(document).on('click', '#clear_cart_btn', handleClearCart);
        $(document).on('click', '#place_order_btn', handlePlaceOrder);
        $(document).on('click', '#modal-add-cart-btn', handleAddToCart);
        $(document).on('change', '#modal-attributes-container .attribute-radio', handleAttributeChange);
        $(document).on('change', '.addon-checkbox', handleAddonCheckbox);
        $(document).on('change', '#user_dropdown', handleUserChange);

        updatePaymentMethodVisibility();
    });

    async function loadSectionServiceType() {
    if (!section_id) return;
    
    try {
        const sectionDoc = await database.collection('sections').doc(section_id).get();
        
        if (sectionDoc.exists) {
            const data = sectionDoc.data();
            const serviceType = data?.serviceType || '';
            isEcommerceService = (serviceType === 'Ecommerce Service');
        }
    } catch (error) {
        console.error("Error loading section serviceType:", error);
        // Keep default false on error (show badge)
    }
}
    
    // ========== CORE FUNCTIONS ==========
    
    async function loadEssentialSettings() {
        // Load settings that are critical for rendering
        const settings = await Promise.all([
            database.collection('settings').doc('placeHolderImage').get(),
            database.collection('settings').doc('DeliveryCharge').get(),
            regionCurrencyRef().get()
        ]);
        
        // Store settings in config
        config.placeholderImage = settings[0].data()?.image || config.placeholderImage;
        state.deliveryChargemain = settings[1].data() || state.deliveryChargemain;
        
        currencyData = settings[2].docs[0]?.data() || {};
        config.currentCurrency = currencyData.symbol || '$';
        config.currencyAtRight = currencyData.symbolAtRight || false;
        config.decimal_degits = currencyData.decimal_degits || 2;
        
        // Load commission settings from localStorage
        const commissionSnapshot = await database.collection('sections').doc(section_id).get();
        if (commissionSnapshot.exists) {
            const parsed = commissionSnapshot.data();
            try {                
                const adminComm = parsed?.adminCommision;
                if (adminComm && (adminComm.enable === true || adminComm.enable === 'true')) {
                    config.isCommissionEnabled = true;
                    config.commissionType = adminComm.type || 'percentage';
                    const value = parseFloat(adminComm.commission);
                    config.commissionValue = isNaN(value) ? 0 : value;
                }
                const platformFee = parsed?.platformFee;
                if(platformFee.enable){
                    platformCharge = platformFee.fee;
                }
            } catch (e) {
                console.warn('Failed to parse commission settings:', e);
            }
        }
    }
    
    async function loadVendor() {
        if (vendorCache.size > 0) {
            renderVendorOptions();
            return;
        }
        
        try {
            const querySnapshot = await refVendor.get();
            const vendorPromises = [];
            
            querySnapshot.docs.forEach(doc => {
                const data = doc.data();
                const vendorId = doc.id;
                
                // Check if vendor has products (cached check)
                const checkPromise = refProducts
                    .where('vendorID', '==', vendorId)
                    .limit(1)
                    .get()
                    .then(snap => {
                        if (!snap.empty) {
                            vendorCache.set(vendorId, {
                                id: vendorId,
                                title: data.title || "Unnamed",
                                data: data
                            });
                        }
                    });
                
                vendorPromises.push(checkPromise);
            });
            
            await Promise.all(vendorPromises);
            renderVendorOptions();
            
        } catch (error) {
            console.error("Error loading vendors:", error);
        }
    }
    
    function renderVendorOptions() {
        let optionsHtml = '<option value="">{{ trans("lang.select_vendor") }}</option>';
        
        vendorCache.forEach(vendor => {
            optionsHtml += `<option value="${vendor.id}">${vendor.title}</option>`;
        });
        
        $('#vendor_dropdown').html(optionsHtml);
    }
    
    async function loadCategories() {
        if (categoryCache.size > 0) {
            renderCategoryOptions();
            return;
        }
        
        try {
            const querySnapshot = await refCategories.get();
            
            querySnapshot.forEach(doc => {
                const data = doc.data();
                categoryCache.set(doc.id, {
                    id: doc.id,
                    title: data.title || "Unnamed"
                });
            });
            
            renderCategoryOptions();
            
        } catch (error) {
            console.error("Error loading categories:", error);
        }
    }
    
    function renderCategoryOptions() {
        let optionsHtml = '<option value="">{{trans("lang.select_categories")}}</option>';
        
        categoryCache.forEach(category => {
            optionsHtml += `<option value="${category.id}">${category.title}</option>`;
        });
        
        $('#category_dropdown').html(optionsHtml);
    }
    
    async function loadUsers() {
        if (userCache.size > 0) {
            renderUserOptions();
            return;
        }
        
        try {
            const querySnapshot = await refUsers
                .where('role', '==', 'customer')
                .where('active', '==', true)
                .get();
            
            querySnapshot.forEach(doc => {
                const data = doc.data();
                userCache.set(doc.id, {
                    id: doc.id,
                    name: `${data.firstName || ''} ${data.lastName || ''}`.trim(),
                    email: data.email || '',
                    phone: data.phoneNumber || '',
                    wallet: data.wallet_amount || 0
                });
            });
            
            renderUserOptions();
            
        } catch (error) {
            console.error("Error loading users:", error);
        }
    }
    
    function renderUserOptions() {
        let optionsHtml = '<option value="">{{trans("lang.search_for_user")}}</option>';
        
        userCache.forEach(user => {
            optionsHtml += `<option value="${user.id}">${user.name}</option>`;
        });
        
        $('#user_dropdown').html(optionsHtml);
    }
    
    async function loadProducts(page = 1) {
        if (state.isProcessing) return;
        if (!state.selectedVendorId) {
            showNoVendorMessage();
            state.isProcessing = false;
            $('#data-table_processing').hide();
            return;
        }
        
        state.isProcessing = true;
        $('#data-table_processing').show();
        
        try {
            let query = refProducts.where('vendorID', '==', state.selectedVendorId);
            
            if (state.selectedCategoryId) {
                query = query.where('categoryID', '==', state.selectedCategoryId);
            }
            
            // Apply search if exists
            if (state.searchText) {
                const allProducts = await query.get();
                const filtered = allProducts.docs.filter(doc => {
                    const data = doc.data();
                    return data.name && data.name.toLowerCase().includes(state.searchText.toLowerCase());
                });
                
                state.totalPages = Math.ceil(filtered.length / config.pageSize);
                const startIndex = (page - 1) * config.pageSize;
                const paginatedDocs = filtered.slice(startIndex, startIndex + config.pageSize);
                
                renderProductList(paginatedDocs, page);
            } else {
                // Paginated query
                query = query.orderBy('name').limit(config.pageSize + 1);
                
                if (page > 1 && state.pageCursors[page - 1]) {
                    query = query.startAfter(state.pageCursors[page - 1]);
                }
                
                const snapshot = await query.get();
                let docs = snapshot.docs; // Use let instead of const
                
                if (docs.length > config.pageSize) {
                    state.pageCursors[page] = docs[config.pageSize - 1];
                    docs = docs.slice(0, config.pageSize);
                } else {
                    state.pageCursors[page] = null;
                }
                
                state.totalPages = state.pageCursors[page] ? page + 1 : page;
                renderProductList(docs, page);
            }

            let vendorDetails = null;
            const vendorSnap = await database.collection('vendors').doc(state.selectedVendorId).get();
            if (vendorSnap.exists) {
                vendorDetails = vendorSnap.data();
                if(packagingChargeEnable){
                    packagingCharge = vendorDetails.packagingCharge ?? '0';
                   
                }
                const vendorAdminComm = vendorDetails?.adminCommission;
                if (vendorAdminComm && (vendorAdminComm.enable === true || vendorAdminComm.enable === 'true')) {
                    config.isCommissionEnabled = true;
                    config.commissionType = vendorAdminComm.type || 'percentage';
                    const value = parseFloat(vendorAdminComm.commission);
                    config.commissionValue = isNaN(value) ? 0 : value;
                }
                let vendorCountry = getCookie('vendorCountryName_'+state.selectedVendorId);
                if (!vendorCountry) {
                    vendorCountry = await getCountryFromLatLng(vendorDetails.latitude,vendorDetails.longitude);
                    setCookie('vendorCountryName_'+state.selectedVendorId, vendorCountry, 365);
                }
                if(vendorCountry != null){
                    const scopes = ['order', 'packaging', 'platform', 'product'];
                    database.collection('tax').where('country', '==', vendorCountry).where('enable', '==', true).where('scope', 'in', scopes).where('sectionId', '==', section_id).get().then(snapshot => {
                        snapshot.forEach(doc => {
                            const tax = doc.data();
                            (taxesByScope[tax.scope] ??= []).push(tax);
                        });
                    });
                }
            }
            
        } catch (error) {
            console.error("Error loading products:", error);
        } finally {
            state.isProcessing = false;
            $('#data-table_processing').hide();
        }
    }
    
    function renderProductList(docs, page) {
        if (docs.length === 0) {
            $('#product-list').html(`
                <div class="col-12 text-center py-5">
                    <p class="text-muted">{{ trans("lang.no_products_found") }}</p>
                </div>
            `);
            $('#pagination-numbers').html('');
            return;
        }
        
        // Render products
        const productHtml = docs.map(doc => {
            const data = doc.data();
            const id = doc.id;
            
            // Try to get from cache first
            if (productCache.has(id)) {
                return productCache.get(id);
            }
            
            const html = generateProductHtml(id, data);
            productCache.set(id, html);
            return html;
        }).join('');
        
        $('#product-list').html(productHtml);
        renderPagination(page);
    }
    
    /* The wholesale tier on the card, so a bulk price is visible while browsing
     * rather than only once the modal is open. A product with variants shows the
     * cheapest tier, matching how the card shows a price range.
     * Kept in step with the store panel's copy. */
    function generateWholesaleHtml(data) {
        if (data.wholesaleEnabled !== true || !data.wholesaleMinQty) {
            return '';
        }

        let candidates = [];

        if (data.item_attribute && data.item_attribute.variants && data.item_attribute.variants.length > 0) {
            data.item_attribute.variants.forEach(function (v) {
                var vw = v.variant_wholesale_price ? v.variant_wholesale_price : data.wholesalePrice;
                if (vw !== undefined && vw !== null && vw !== '') {
                    candidates.push(parseFloat(vw));
                }
            });
        } else if (data.wholesalePrice !== undefined && data.wholesalePrice !== null && data.wholesalePrice !== '') {
            candidates.push(parseFloat(data.wholesalePrice));
        }

        if (candidates.length === 0) {
            return '';
        }

        const label = "{{ trans('lang.wholesale_from_units') }}".replace(':count', data.wholesaleMinQty);

        return `<div class="shop-wholesale mb-2"><span class="badge badge-info">{{ trans('lang.wholesale') }} ` +
            `${formatPrice(Math.min(...candidates))} ${label}</span></div>`;
    }

    function generateProductHtml(id, data) {
        const name = data.name || 'Unnamed';
        const photo = data.photo || config.placeholderImage;
        // add new line to show veg/non-veg badge
        status = '{{ trans('lang.non_veg') }}';
            statusclass = 'closed';
            if (data.veg == true) {
                status = '{{ trans('lang.veg') }}';
                statusclass = 'open';
            }

        const route1 = '{{route("items.edit",":id")}}'.replace(':id', id);
        
        // Calculate price display
        const priceHtml = generatePriceHtml(data);
        const wholesaleHtml = generateWholesaleHtml(data);

        let statusHtml = '';
        if (!isEcommerceService) {
            statusHtml = `
                <div class="member-plan position-absolute">
                    <span class="badge badge-dark ${statusclass}">${status}</span>
                </div>
            `;
        }

        return `
            <div class="text-center col-md-4 col-lg-4 mb-4">
                <div class="shop-item h-100">
                    <h3 class="shop-item-title mb-2 h6">
                        <a href="${route1}" class="text-dark text-decoration-none">${name}</a>
                    </h3>
                        ${statusHtml}
                    <div class="shop-item-image mb-3">
                        <a href="${route1}">
                            <img class="item-image img-fluid" 
                                 src="${photo}" 
                                 alt="${name}"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='${config.placeholderImage}';">
                        </a>
                    </div>
                    <div class="shop-price mb-3">
                        <span class="form-control text-center text-dark">${priceHtml}</span>
                    </div>
                    ${wholesaleHtml}
                    <div class="shop-item-btn">
                        <button class="btn btn-primary btn-sm add-to-cart" data-id="${id}">
                            {{trans('lang.add_to_cart')}}
                        </button>
                    </div>
                    ${generateHiddenInputs(id, data)}
                </div>
            </div>
        `;
    }
    
    function generatePriceHtml(data) {
        let displayPrice = '';
        const originalPrice = parseFloat(data.price || 0);
        const disPrice = parseFloat(data.disPrice || 0);
        
        if (data.item_attribute?.variants?.length > 0) {
            // Handle variants
            const variantPrices = data.item_attribute.variants.map(v => parseFloat(v.variant_price || 0));
            const minPrice = Math.min(...variantPrices);
            const maxPrice = Math.max(...variantPrices);
            
            if (minPrice === maxPrice) {
                displayPrice = formatPrice(minPrice);
            } else {
                displayPrice = `${formatPrice(minPrice)} - ${formatPrice(maxPrice)}`;
            }
        } else if (disPrice > 0 && disPrice < originalPrice) {
            // Handle discounted price
            displayPrice = `
                <span class="text-danger">${formatPrice(disPrice)}</span>
                <small class="text-muted"><s>${formatPrice(originalPrice)}</s></small>
            `;
        } else {
            // Regular price
            displayPrice = formatPrice(originalPrice);
        }
        
        return displayPrice;
    }
    
    /* Shows the wholesale tier in the add-to-cart modal, so the counter can see a
     * bulk price exists before adding the line, rather than discovering it only
     * once the quantity crosses the threshold.
     *
     * Reads the hidden inputs the product card already carries.
     * variantWholesalePrice is the selected variant's own price when it has one;
     * it wins over the product's, exactly as the cart resolves it.
     * Kept in step with the store panel's copy. */
    function showModalWholesale(productId, variantWholesalePrice) {
        if ($('#wholesale_enabled_' + productId).val() !== '1') {
            $('#modal_wholesale_line').hide();
            return;
        }

        var price = (variantWholesalePrice !== undefined && variantWholesalePrice !== null && variantWholesalePrice !== '') ?
            variantWholesalePrice : $('#wholesale_price_' + productId).val();
        var minQty = $('#wholesale_min_qty_' + productId).val();

        if (price === undefined || price === null || price === '' || !minQty) {
            $('#modal_wholesale_line').hide();
            return;
        }

        var label = "{{ trans('lang.wholesale_from_units') }}".replace(':count', minQty);

        $('#modal_wholesale_value').text(formatPrice(parseFloat(price)) + ' ' + label);
        $('#modal_wholesale_line').show();
    }

    function formatPrice(amount) {
        const formatted = parseFloat(amount).toFixed(config.decimal_degits);
        return config.currencyAtRight 
            ? `${formatted}${config.currentCurrency}` 
            : `${config.currentCurrency}${formatted}`;
    }
    
    function generateHiddenInputs(id, data) {
        // Calculate original price for cart (without commission)
        let originalCartPrice = parseFloat(data.price || 0);
        if (data.disPrice && data.disPrice !== '0' && data.disPrice !== "") {
            originalCartPrice = parseFloat(data.disPrice);
        }
        
        // IMPORTANT: Use the same ID format as your original code
        return `
            <input type="hidden" id="name_${id}" value="${data.name || ''}">
            <input type="hidden" id="description_${id}" value='${data.description || ''}'>
            <input type="hidden" id="price_${id}" value="${data.price || 0}">
            <input type="hidden" id="dis_price_${id}" value="${originalCartPrice}">
            <input type="hidden" id="discount_${id}" value="${data.discount || 0}">
            <input type="hidden" id="quantity_${id}" value="${data.quantity || 0}">
            <input type="hidden" id="image_${id}" value="${data.photo || ''}">
            <input type="hidden" id="category_id_${id}" value="${data.categoryID || ''}">
            <input type="hidden" id="vendor_id_${id}" value="${data.vendorID || ''}">
            <input type="hidden" id="item_attribute_${id}" value='${JSON.stringify(data.item_attribute || {})}'>
            <input type="hidden" id="addons_price_${id}" value='${JSON.stringify(data.addOnsPrice || [])}'>
            <input type="hidden" id="addons_title_${id}" value='${JSON.stringify(data.addOnsTitle || [])}'>
            <input type="hidden" id="taxSetting_${id}" value='${JSON.stringify(data.taxSetting || [])}'>
            <input type="hidden" id="wholesale_enabled_${id}" value="${data.wholesaleEnabled === true ? '1' : '0'}">
            <input type="hidden" id="wholesale_price_${id}" value="${data.wholesalePrice || ''}">
            <input type="hidden" id="wholesale_min_qty_${id}" value="${data.wholesaleMinQty || ''}">
        `;
    }
    
    // ========== UI HELPER FUNCTIONS ==========
    
    function showNoVendorMessage() {
        $('#product-list').html(`
            <div class="col-12 text-center py-5">
                <p class="text-muted">
                    {{ trans('lang.select_store_to_browse') }}
                </p>
            </div>
        `);
        $('#pagination-numbers').html('');
    }
    
    function renderPagination(currentPage) {
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link pagination-link" href="#" data-page="${currentPage - 1}">
                    {{trans('lang.previous')}}
                </a>
            </li>`;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(state.totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link pagination-link" href="#" data-page="${i}">${i}</a>
                </li>`;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage >= state.totalPages ? 'disabled' : ''}">
                <a class="page-link pagination-link" href="#" data-page="${currentPage + 1}">
                    {{trans('lang.next')}}
                </a>
            </li>`;
        
        $('#pagination-numbers').html(paginationHtml);
    }
    
    function initializeSelect2() {
        $('#user_dropdown, #vendor_dropdown, #category_dropdown').select2({
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder');
            }
        });
    }
    
    function setupEventListeners() {
        // Category change
        $('#category_dropdown').on('change', function () {
            state.selectedCategoryId = $(this).val();
            state.currentPage = 1;
            state.pageCursors = {};
            loadProducts(1);
        });
        
        // Vendor change with confirmation
        $('#vendor_dropdown').on('change', async function () {
            const newVendorId = $(this).val() || '';  
            
            if (!newVendorId) {
                state.confirmedVendorId = null;
                state.selectedVendorId = null;
                state.currentPage = 1;
                state.pageCursors = {};
                showNoVendorMessage();
                return;
            }
            if (state.confirmedVendorId === newVendorId) return;

            const cartHasItems = $('#cart_list tr').length > 0 && 
                                $('#cart_list td[colspan="4"]').length === 0;

            if (cartHasItems) {
                const result = await Swal.fire({
                    text: "{{ trans('lang.change_vendor_cart_clear_warning') }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "{{ trans('lang.okay') }}",
                    cancelButtonText: "{{ trans('lang.cancel') }}",
                });

                if (!result.isConfirmed) {                    
                    $(this).val(state.confirmedVendorId || '').trigger('change.select2');
                    return;
                }

                await clearCartAjax();
            }
            state.confirmedVendorId = newVendorId;
            state.selectedVendorId = newVendorId;
            state.currentPage = 1;
            state.pageCursors = {};
            await loadProducts(1);
        });
        
        // Search with debounce
        let searchTimeout;
        $('#search-product').on('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                state.searchText = $(this).val().trim();
                state.currentPage = 1;
                state.pageCursors = {};
                loadProducts(1);
            }, 500);
        });        
       
        // Clear filters
        $('#clear_filter').on('click', function () {
            $('#vendor_dropdown').val('').trigger('change.select2');
            $('#category_dropdown').val('').trigger('change.select2');
            $('#search-product').val('');
            
            state.selectedCategoryId   = null;
            state.selectedVendorId = null;
            state.confirmedVendorId = null;
            state.searchText           = "";
            state.currentPage          = 1;
            state.pageCursors          = {};
            
            showNoVendorMessage();
            
            $('#data-table_processing').hide();
        });
        
        // Clear user search
        $('#clear_user_search').on('click', function () {
            $('#user_dropdown').val('').trigger('change');
            $('#user_detail_block').addClass('d-none');
        });
        
        // Pagination links (event delegation)
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page >= 1 && page <= state.totalPages) {
                loadProducts(page);
            }
        });
    }
    
    // ========== CART FUNCTIONS ==========
    
    async function clearCartAjax() {
        return $.ajax({
            url: '{{ route('clear.cart') }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                if (response.success) {
                    $('#cart_list').html(`
                        <tr>
                            <td colspan="4" class="text-center">
                                {{ trans('lang.cart_is_empty') }}
                            </td>
                        </tr>
                    `);
                    $('#cart_total').empty();
                    $('.shop-payment-method').hide();
                }
            }
        });
    }
    
    function formatCartPrices() {
        $('.cart-price').each(function () {
            const rawPrice = $(this).data('price') || 0;
            const formatted = parseFloat(rawPrice).toFixed(config.decimal_degits);
            
            if (config.currencyAtRight) {
                $(this).text(formatted + config.currentCurrency);
            } else {
                $(this).text(config.currentCurrency + formatted);
            }
        });
    }
    
    // ========== ERROR HANDLING ==========
    
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            timer: 3000
        });
    }
    
    // ========== ADD TO CART AND RELATED FUNCTIONS ==========
    
    async function getVendorName(vendorId) {
        if (!vendorId) return '';
        
        // Check cache first
        const cached = vendorCache.get(vendorId);
        if (cached) return cached.title;
        
        try {
            const doc = await database.collection('vendors').doc(vendorId).get();
            if (doc.exists) {
                const data = doc.data();
                return data.title || '';
            }
        } catch (error) {
            console.error("Error fetching Vendor:", error);
        }
        return '';
    }
    
    async function getVariantsHtml(productData, attributes, variants) {
        let attributesHtml = '';
        
        for (const attribute of attributes) {
            const attributeHtml = await getAttributeHtml(productData, attribute);
            attributesHtml += attributeHtml;
        }
        
        // Initialize first variant selection
        setTimeout(() => {
            $('#modal-attributes-container .attribute-radio:checked')
                .first()
                .trigger('change');
        }, 0);
        
        return attributesHtml;
    }    
    
    async function getAttributeHtml(vendorProduct, attribute) {
        const attributeId = attribute.attribute_id;
      
        try {
            const attributeDoc = await database.collection('vendor_attributes').doc(attributeId).get();
            if (!attributeDoc.exists) return '';

            const attributeInfo = attributeDoc.data();
            let html = `<div class="attribute-drp col-md-6" data-aid="${attributeId}" data-atitle="${attributeInfo.title}">`;
            html += `<h4 class="attribute-label">${attributeInfo.title}</h4>`;
            html += '<div class="attribute-options">';

            $.each(attribute.attribute_options, function (i, option) {
                const inputId = `attribute-${attributeId}-${i}-${Date.now()}`; 
               
                const checked = i === 0 ? ' checked' : ''; 

                html += `
                    <div class="custom-control custom-radio border-bottom py-2 attribute-selection">
                        <input type="radio"
                            id="${inputId}"
                            name="attribute-options-${attributeId}-${Date.now()}"   // ← make name unique per modal open!
                            class="custom-control-input attribute-radio"
                            data-atitle="${attributeInfo.title}"
                            value="${option}"
                            ${checked}>
                        <label class="custom-control-label" for="${inputId}">${option}</label>
                    </div>`;
            });

            html += '</div></div>';
            

            return html;

        } catch (error) {
            console.error("Error fetching attribute:", error);
            return '';
        }
    }
    
    // ========== EVENT HANDLERS ==========
    
    // Add to cart button click
    $(document).on('click', '.add-to-cart', async function () {
        const productId = $(this).data('id');

        /* Cleared up front: the modal is reused between products, so a tier left
         * from the last one must not survive into this one. */
        $('#modal_wholesale_line').hide();
        
        // Get product data from hidden inputs
        const name = $(`#name_${productId}`).val() || 'N/A';
        const description = $(`#description_${productId}`).val() || '';
        const price = $(`#price_${productId}`).val() || '0';
        const discount = $(`#discount_${productId}`).val() || '0';
        const discountPrice = $(`#dis_price_${productId}`).val() || '0';
        const quantity = $(`#quantity_${productId}`).val() || '0';
        const photo = $(`#image_${productId}`).val() || '';
        const categoryId = $(`#category_id_${productId}`).val() || '';
        const vendorId = $(`#vendor_id_${productId}`).val() || '';
        
        let itemAttributes = {};
        try {
            const raw = $(`#item_attribute_${productId}`).val();
            itemAttributes = JSON.parse(raw || '{}');
        } catch (e) {
            console.error("Invalid JSON:", raw);
        }
        
        // Calculate display price
        const originalPrice = parseFloat(price || 0);
        const disPrice = parseFloat(discountPrice || 0);
        
        let priceHtml = '';
        if (disPrice > 0 && disPrice < originalPrice) {
            const formattedDis = formatPrice(disPrice);
            const formattedOriginal = formatPrice(originalPrice);
            priceHtml = `<span class="text-danger font-weight-bold">${formattedDis}</span>
                         <s class="text-muted ml-2">${formattedOriginal}</s>`;
        } else {
            priceHtml = `<span>${formatPrice(originalPrice)}</span>`;
        }
        
        // Update modal content
        const imgSrc = photo && photo !== '' ? photo : config.placeholderImage;
        $('#modal-product-image')
            .attr('src', imgSrc)
            .off('error')
            .on('error', function () {
                $(this).attr('src', config.placeholderImage);
            });
        
        const descriptionHtml = description;
        $('#modal-product-name').text(name);
        $('#modal-product-price').text(
            description.length > 28 ? description.slice(0, 28) + '...' : description
        );
        let displayPrice = disPrice > 0 && disPrice < originalPrice ? formatPrice(disPrice) : formatPrice(originalPrice);
        $('#variant_price').html(displayPrice);
        showModalWholesale(productId);

        // $('#variant_price').html(priceHtml);
        
        // Quantity display
        $('.modal-product-quantity-count').text(quantity == -1 ? 'Unlimited' : quantity);
        
        // Vendor name
        if (vendorId) {
            const vendorName = await getVendorName(vendorId);
            $('.modal-product-vendor-name').text(vendorName);
        } else {
            $('.modal-product-vendor-name').text("-");
        }
        
        // Store product ID for later use
        $('#modal-add-cart-btn').data('product-id', productId);        
        
        // Handle variants if exists
        if (itemAttributes.attributes && itemAttributes.variants && 
            itemAttributes.attributes.length > 0 && itemAttributes.variants.length > 0) {
            
            const productData = {
                id: productId,
                name: name,
                price: price,
                discount: discount,
                stock_quantity: quantity,
                quantity: 1,
                image: photo,
                category_id: categoryId,
                vendor_id: vendorId,
                item_attribute: itemAttributes
            };
            
            const variantHtml = await getVariantsHtml(productData, itemAttributes.attributes, itemAttributes.variants);
            $('#modal-attributes-container').html(variantHtml);
        } else {
            $('#modal-attributes-container').html('');
            // Clear variant inputs when product has no variants
            $('#selected_variant_id').val('');
            $('#selected_variant_price').val('');
        }        
       
        // Handle addons
        let addonsPrice = [];
        let addonsTitle = [];
        try {
            addonsPrice = JSON.parse($(`#addons_price_${productId}`).val() || '[]');
            addonsTitle = JSON.parse($(`#addons_title_${productId}`).val() || '[]');
        } catch (e) {
            console.error('Invalid addon JSON');
        }

        if (addonsPrice.length > 0) {
            let addonsHtml = `<div class="addons mt-2 mb-3">
                                <h5 class="font-weight-bold mb-3">{{ trans('lang.addons_title') }}</h5>`;
            
            for (let i = 0; i < addonsPrice.length; i++) {
                let originalAddonPrice = parseFloat(addonsPrice[i] || 0);
                let formattedPrice = formatPrice(originalAddonPrice);
                
                // Extract title safely
                let addonTitle = '';
                if (typeof addonsTitle[i] === 'string') {
                    addonTitle = addonsTitle[i];
                } else if (addonsTitle[i] && typeof addonsTitle[i] === 'object') {
                    // If it's an object, try to get title property
                    addonTitle = addonsTitle[i].title || addonsTitle[i].name || '';
                } else {
                    addonTitle = String(addonsTitle[i] || '');
                }
                
                addonsHtml += `
                    <div class="form-group row width-100 border-bottom p-0">
                        <div class="form-check width-100 pl-0">
                            <input type="checkbox"
                                class="form-check-input addon-checkbox"
                                id="addon_${productId}_${i}"
                                data-price="${originalAddonPrice}"
                                value="${addonTitle.replace(/"/g, '&quot;')}">
                            <label class="control-label d-flex justify-content-between w-100" 
                                for="addon_${productId}_${i}">
                                <span>${addonTitle}</span>
                                <span>+${formattedPrice}</span>
                            </label>
                        </div>
                    </div>`;
            }
            
            addonsHtml += `</div>`;
            $('#modal-addons-container').html(addonsHtml);
        } else {
            $('#modal-addons-container').html('');
        }
        
        // Show modal
        $('#addToCartModal').modal('show');
    });
    
    // Handle variant attribute change
    function handleAttributeChange() {
        const productId = $('#modal-add-cart-btn').data('product-id');
        const raw = $(`#item_attribute_${productId}`).val();
        
        if (!raw) return;
        
        let itemAttributes = JSON.parse(raw);
        let selectedValues = {};
        let skuParts = [];
        
        $('#modal-attributes-container .attribute-drp').each(function () {
            const title = $(this).data('atitle');
            const value = $(this).find('.attribute-radio:checked').val();
            if (value) {
                selectedValues[title] = value;
                skuParts.push(value);
            }
        });
        
        if (skuParts.length !== $('#modal-attributes-container .attribute-drp').length) {
            return;
        }
        
        const selectedSku = skuParts.join('-');
        const matchedVariant = itemAttributes.variants.find(v => v.variant_sku === selectedSku);
        
        if (!matchedVariant) {
            $('#variant_price').text('');
            $('.modal-product-quantity-count').text('');
            showModalWholesale(productId);
            return;
        }
        
        const rawPrice = parseFloat(matchedVariant.variant_price || 0);
        const variantPrice = rawPrice; // Apply commission here if needed
        
        let addonTotal = 0;
        $('.addon-checkbox:checked').each(function () {
            addonTotal += parseFloat($(this).data('price') || 0);
        });
        let totalWithAddons =  parseFloat(addonTotal) +  parseFloat(variantPrice);
        const priceDisplay = formatPrice(totalWithAddons);
        
        $('#variant_price')
            .html(priceDisplay)
            .attr({
                'data-vid': matchedVariant.variant_id,
                'data-vprice': variantPrice,
                'data-vqty': matchedVariant.variant_quantity,
                'data-vsku': matchedVariant.variant_sku,
                'data-vimg': matchedVariant.variant_image || '',
                'data-vinfo': JSON.stringify(selectedValues)
            });
        
        showModalWholesale(productId, matchedVariant.variant_wholesale_price);

        $('#selected_variant_id').val(matchedVariant.variant_id);
        $('#selected_variant_price').val(variantPrice);
        
        $('.modal-product-quantity-count').text(
            matchedVariant.variant_quantity === '-1' ? 'Unlimited' : matchedVariant.variant_quantity
        );
    }
   
    // Handle addon checkbox change
    function handleAddonCheckbox() {
        const productId = $('#modal-add-cart-btn').data('product-id');
        
        let basePrice = 0;
        
       
        const hasAttributes = $('#modal-attributes-container .attribute-drp').length > 0;
        
        if (hasAttributes) {           
            basePrice = parseFloat($('#selected_variant_price').val() || 0);
        } else {            
            basePrice = parseFloat($(`#dis_price_${productId}`).val() || 0);
        }        
       
        if (basePrice === 0) {
            basePrice = parseFloat($(`#price_${productId}`).val() || 0);
        }
        
        let addonTotal = 0;
        
        $('.addon-checkbox:checked').each(function () {
            addonTotal += parseFloat($(this).data('price'));
        });
        
        let finalPrice = basePrice + addonTotal;
        let display = formatPrice(finalPrice);
        
        $('#variant_price').text(display);
    }
    
    // Handle add to cart from modal
    async function handleAddToCart() {
        const productId = $(this).data('product-id');
        
        @guest
            window.location.href = '{{ route('login') }}';
            return false;
        @endguest
        
        const quantity = 1;
        const productQty = $(`#quantity_${productId}`).val();
        
        if (productQty == 0) {
            Swal.fire({ text: "{{trans('lang.invalid_qty')}}", icon: "error" });
            return false;
        }
        
        // Declare these as let instead of const since they might be modified
        let discount = parseFloat($(`#discount_${productId}`).val() || 0);
        let dis_price = parseFloat($(`#dis_price_${productId}`).val() || 0);
        const name = $(`#name_${productId}`).val();
        const description = $(`#description_${productId}`).val();
        const stock_quantity = $(`#quantity_${productId}`).val();
        const image = $(`#image_${productId}`).val() || config.placeholderImage;
        const category_id = $(`#category_id_${productId}`).val();
        
        let raw = $(`#item_attribute_${productId}`).val();
        let itemAttributes = {};
        try {
            itemAttributes = JSON.parse(raw || '{}');
        } catch (e) {
            console.error("Invalid JSON:", raw);
        }
        
        let hasVariant = itemAttributes.attributes && itemAttributes.variants && 
                        itemAttributes.attributes.length > 0 && itemAttributes.variants.length > 0;
        
        let variant_info = {};
        let variantWholesalePrice = '';
        let originalBasePrice = 0;
        
        // Determine original base price
        if (hasVariant) {
            let selectedOptions = [];
            $('#modal-attributes-container .attribute-options').each(function () {
                const selected = $(this).find('input[type="radio"]:checked').val();
                if (selected) selectedOptions.push(selected.trim());
            });
            
            let totalAttributes = itemAttributes.attributes.length;
            let selectedCount = selectedOptions.length;
            
            if (selectedCount === 0) {
                // No variant selected → use main product
                variant_info = {};
            } else if (selectedCount < totalAttributes) {
                Swal.fire({
                    text: "{{trans('lang.select_all_variant_attributes')}}",
                    icon: "error"
                });
                return false;
            } else {
                // All attributes selected → find matching variant
                let normalizedSelectedSku = selectedOptions.join('-').toLowerCase().replace(/\s+/g, '');
                let matchedVariant = null;
                
                itemAttributes.variants.forEach(v => {
                    let variantSku = (v.variant_sku || '').toLowerCase().replace(/\s+/g, '');
                    if (variantSku === normalizedSelectedSku) {
                        matchedVariant = v;
                    }
                });
                
                if (!matchedVariant || !matchedVariant.variant_id) {
                    Swal.fire({ text: "{{trans('lang.no_valid_variant_matched')}}", icon: "error" });
                    return false;
                }
                
                let variant_qty = parseInt(matchedVariant.variant_quantity);
                if (quantity > variant_qty && variant_qty != -1) {
                    Swal.fire({ text: "{{trans('lang.invalid_qty')}}", icon: "error" });
                    return false;
                }
                
                // Fetch attribute titles for display
                let variant_options_map = {};
                for (let index = 0; index < itemAttributes.attributes.length; index++) {
                    const attribute = itemAttributes.attributes[index];
                    const selected = selectedOptions[index];
                    
                    try {
                        const vendorAttrSnap = await database.collection('vendor_attributes')
                            .doc(attribute.attribute_id)
                            .get();
                        
                        const title = vendorAttrSnap.exists ? vendorAttrSnap.data().title : attribute.attribute_id;
                        variant_options_map[title] = selected;
                    } catch (error) {
                        console.error("Error fetching vendor attribute:", error);
                    }
                }
                
                originalBasePrice = parseFloat(matchedVariant.variant_price || 0);
                if (matchedVariant.variant_wholesale_price) {
                    variantWholesalePrice = matchedVariant.variant_wholesale_price;
                }
                variant_info = {
                    variant_id: matchedVariant.variant_id,
                    variant_sku: matchedVariant.variant_sku,
                    variant_price: originalBasePrice,
                    variant_qty: matchedVariant.variant_quantity,
                    variant_image: matchedVariant.variant_image ?? config.placeholderImage,
                    variant_options: variant_options_map
                };
                
                // These are now let variables, so they can be reassigned
                discount = 0;
                dis_price = 0;
            }
        }
        
        // If no variant or none selected → use main product price
        if (originalBasePrice === 0) {
            let rawPrice = parseFloat($(`#price_${productId}`).val() || 0);
            if (dis_price > 0) {
                rawPrice = dis_price;
            }
            originalBasePrice = rawPrice;
        } 

        /* The quantity break travels with the line so the cart can reprice it when
         * the quantity changes, rather than only at the moment it is added. A
         * variant's own wholesale price wins over the product's. */
        let wholesaleEnabled = $('#wholesale_enabled_' + productId).val() === '1';
        let wholesalePrice = wholesaleEnabled ?
            (variantWholesalePrice !== '' ? variantWholesalePrice : $('#wholesale_price_' + productId).val()) : '';
        let wholesaleMinQty = wholesaleEnabled ? $('#wholesale_min_qty_' + productId).val() : '';

        let selectedAddons = [];
        let selectedAddonsTotal = 0;

        $('.addon-checkbox:checked').each(function () {
            let addonPrice = parseFloat($(this).data('price') || 0);
            let addonTitle = String($(this).val() || '').trim();
            
            // Ensure we have a valid title (not empty string)
            if (addonTitle && addonTitle !== '') {
                selectedAddons.push({
                    title: addonTitle,
                    price: addonPrice
                });
                selectedAddonsTotal += addonPrice;
            }
        });
        
        let priceForSession = originalBasePrice + selectedAddonsTotal;
        let totalPriceForSession = priceForSession * quantity;       
        
        let productTaxSetting = $('#taxSetting_' + productId).val();
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: "{{ route('add-to-cart') }}",
            data: {
                _token: '{{ csrf_token() }}',
                id: productId,
                quantity: quantity,
                stock_quantity: stock_quantity,
                name: name,
                original_base_price: originalBasePrice,
                price: totalPriceForSession,
                dis_price: dis_price,
                discount: discount,
                image: image,
                item_price: priceForSession,
                variant_info: JSON.stringify(variant_info),
                category_id: category_id,
                decimal_degits: config.decimal_degits,
                addons: JSON.stringify(selectedAddons),
                addons_total: selectedAddonsTotal,
                commission_enabled: config.isCommissionEnabled ? '1' : '0',
                commission_type: config.commissionType,
                commission_value: config.commissionValue,
                taxSetting: productTaxSetting,
                wholesale_price: wholesalePrice,
                wholesale_min_qty: wholesaleMinQty,
                taxScope: taxScope,
                taxesByScope: taxesByScope,
                packagingCharge: packagingCharge,
                platformCharge: platformCharge,
                currencyData: currencyData,
                vendor_id: state.selectedVendorId,
            },
            success: function (response) {
                $('#cart_list').html(response.html);
                $('#cart_total').html(response.total);
                formatCartPrices();
                loadCurrency();
                $('#addToCartModal').modal('hide');

                updatePaymentMethodVisibility();

                Swal.fire({
                    text: "{{trans('lang.item_added_to_cart')}}",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr, status, error) {
                console.error('Error adding to cart:', error);
                Swal.fire({
                    text: "{{trans('lang.failed_to_add_item')}}",
                    icon: "error"
                });
            }
        });
    }
    
    // Load currency display
    function loadCurrency() {
        if (config.currencyAtRight) {
            $('.currency-symbol-left').hide();
            $('.currency-symbol-right').show();
            $('.currency-symbol-right').text(config.currentCurrency);
        } else {
            $('.currency-symbol-left').show();
            $('.currency-symbol-right').hide();
            $('.currency-symbol-left').text(config.currentCurrency);
        }
    }
    
    // Handle update cart
    function handleUpdateCart() {
        const index = $(this).data('index');
        const operation = $(this).data('operation');
        
        $.ajax({
            url: '{{ route("cart.update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                index: index,
                operation: operation,
                commission_enabled: config.isCommissionEnabled ? '1' : '0',
                commission_type: config.commissionType,
                commission_value: config.commissionValue,
            },
            success: function (res) {
                $('#cart_list').html(res.html);
                $('#cart_total').html(res.total);
                formatCartPrices();

                updatePaymentMethodVisibility();

                if (res.error) {
                    Swal.fire({
                        icon: 'warning',
                        text: res.error,
                    });
                }
            }
        });
    }
    
    // Handle delete cart item
    function handleDeleteCartItem() {
        const index = $(this).data('index');
        
        $.ajax({
            url: '{{ route("cart.remove",["index" => ":index"]) }}'.replace(":index", index),
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.empty) {
                    $('#cart_list').html(`
                        <tr>
                            <td colspan="4" class="text-center">{{trans('lang.cart_is_empty')}}</td>
                        </tr>
                    `);
                    $('#cart_total').empty();
                    $('.shop-payment-method').hide();
                    return;
                }
                
                $('#cart_list').html(res.html);
                $('#cart_total').html(res.total);
                formatCartPrices();

                updatePaymentMethodVisibility();
            }
        });
    }
    
    // Handle clear cart
    function handleClearCart() {
        clearCartAjax().then(() => {
            Swal.fire({
                text: "{{ trans('lang.cart_has_been_cleared') }}",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            });
            updatePaymentMethodVisibility();
        });
    }
    
    // Handle user change
    function handleUserChange() {
        const userId = $(this).val();
        
        if (!userId) {
            $('#user_detail_block').addClass('d-none');
            return;
        }
        
        const user = userCache.get(userId);
        if (user) {
            $('#user_detail_name').text(user.name);
            $('#user_detail_email').text(user.email);
            $('#user_detail_phone').text(user.phone);
            $('#user_detail_wallet').text('$ ' + parseFloat(user.wallet).toFixed(2));
            $('#user_detail_block').removeClass('d-none');
        } else {
            $('#user_detail_block').addClass('d-none');
        }
    }
    
    // Handle place order
    async function handlePlaceOrder() {
        const response = await fetch('/get-session-cart');
        const cart = await response.json();

        const selectedUser = $('#user_dropdown').val();
        const selectedPaymentMethod = $('input[name="paymentmethod"]:checked').val();

        const orderVendorID = $('#vendor_dropdown').val();        

        if (!orderVendorID) {
            Swal.fire({
                icon: 'error',
                text: '{{trans("lang.vendor_not_found_for_cart_items")}}',
            });
            return;
        }
                
        if (
            !cart ||
            !cart.item ||
            typeof cart.item !== 'object' ||
            Object.keys(cart.item).length === 0 ||
            (Array.isArray(cart.item) && cart.item.length === 0)
        ){
            Swal.fire({
                icon: 'warning',
                text: "{{trans('lang.cart_is_empty_error')}}",
            });
            return;
        }

       if (!selectedUser) {
            Swal.fire({
                icon: 'warning',
                text: "{{trans('lang.select_user_before_placing_order')}}",
            });
            return;
        }

        if (!selectedPaymentMethod) {
            Swal.fire({
                icon: 'warning',
                text: "{{trans('lang.please_select_payment_method')}}",
            });
            return;
        }

        $('#data-table_processing').show();

        var id_order = database.collection('vendor_orders').doc().id;
      
        var userSnapshot = await database.collection('users').doc(selectedUser).get();
        var userDetails = '';
        let defaultShippingAddress = null;
        if (userSnapshot.exists) {
            userDetails = userSnapshot.data();

            const shippingAddresses = userDetails.shippingAddress || [];

            if (Array.isArray(shippingAddresses)) {
                defaultShippingAddress = shippingAddresses.find(addr => addr && addr.isDefault === true) || null;
            }
        }

        let vendorDetails = null;
        const vendorSnap = await database.collection('vendors').doc(orderVendorID).get();
        if (vendorSnap.exists) {
            vendorDetails = vendorSnap.data();
        } else {
            Swal.fire({
                icon: 'error',
                text: '{{trans("lang.vendor_not_found")}}',
            });
            return;
        }

        let products = [];

        for (const [vendorId, vendorItems] of Object.entries(cart.item)) {

            for (const [productId, item] of Object.entries(vendorItems)) {

                let product = {
                    id: item.id || null,
                    name: item.name || '',
                    category_id: item.category_id || '',
                    price: String(applyCommission(item.original_base_price || item.price || 0)),
                    // discountPrice: String(applyCommission(item.dis_price || 0)),
                    discountPrice: String(
                        (item.variant_info && item.variant_info.variant_id) ? 0 : (applyCommission(item.discountPrice || item.dis_price || 0))
                    ),
                    quantity: parseInt(item.quantity) || 1,
                    photo: item.image || '',
                    vendorID: orderVendorID,
                    variant_info: null,
                    extras: item.extras ?? [],
                    extras_price: String(applyCommission(item.extras_price ?? 0)),
                    taxSetting: item.taxSetting ?? [],
                    /* The order records the tier it was actually charged at. A
                     * store changing its wholesale price later must never alter
                     * what a past order says it charged. */
                    isWholesale: item.is_wholesale === true,
                    wholesaleMinQty: item.wholesale_min_qty ?? '',
                };

                if (item.variant_info) {
                    let variantInfo = item.variant_info;
                    if (typeof variantInfo === 'string') {
                        try {
                            variantInfo = JSON.parse(variantInfo);
                        } catch (e) {
                            console.error('Invalid variant_info JSON', e);
                            variantInfo = {};
                        }
                    }
                    if (variantInfo.variant_id) {
                        product.variant_info = {
                            variant_id: variantInfo.variant_id || '',
                            variant_image: variantInfo.variant_image || '',
                            variant_options: variantInfo.variant_options || [],
                            variant_price: String(variantInfo.variant_price || ''),
                            variant_qty: String(variantInfo.variant_qty || ''),
                            variant_sku: variantInfo.variant_sku || ''
                        };
                    }
                }
                products.push(product);
            }
        }

        var taxSetting = cart.taxSetting ? cart.taxSetting : [];
        var taxScope = cart.taxScope ? cart.taxScope : 'order';
        var driverDeliveryTax = (cart.taxesByScope && cart.taxesByScope.delivery) ? cart.taxesByScope.delivery : [];
        var packagingTax = (cart.taxesByScope && cart.taxesByScope.packaging) ? cart.taxesByScope.packaging : [];
        var platformTax = (cart.taxesByScope && cart.taxesByScope.platform) ? cart.taxesByScope.platform : [];
        var platformCharge = cart.platformCharge ? cart.platformCharge : '0';
       
        let orderData = {
            "createdAt": firebase.firestore.FieldValue.serverTimestamp(),            
            'deliveryCharge': "0",
            'discount': 0,
            'couponCode': "",
            'couponId': "",
            'notes': "",
            'driverID': null,            
            'scheduleTime': null,
            'id': id_order,
            'payment_method': selectedPaymentMethod,           
            'adminCommission': String(config.commissionValue),
            'adminCommissionType': config.commissionType,
            'products': products,
            'tip_amount': "0.0",
            'takeAway': false,
            'status': "Order Completed",           
            "taxSetting": taxSetting,             
            "author":userDetails,
            "authorID": selectedUser,           
            'vendor': vendorDetails,          
            'vendorID': orderVendorID,
            'isPosOrder': true,
            'taxScope': taxScope,
            "driverDeliveryTax": driverDeliveryTax,
            "packagingTax": packagingTax,
            "platformFee": platformCharge,
            "platformTax": platformTax,
            "section_id": section_id,
            "packagingChargeEnable": packagingChargeEnable,
        };

        /* The order inherits the region of the store it was placed against, so
         * it stays with that store when the list is filtered by region. */
        if (vendorDetails && vendorDetails.regionId) {
            orderData.regionId = vendorDetails.regionId;
        } else {
            orderData = withRegion(orderData);
        }

        if (defaultShippingAddress) {
            orderData.address = defaultShippingAddress;
        }

        await manageInventory(products);
        
        await walletTransaction(products, cart, id_order, orderVendorID, vendorDetails);

        await database.collection('vendor_orders').doc(id_order).set(
           orderData
        ).then(() => {

            $.ajax({
                url: "{{ route('clear.cart') }}", 
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: "{{trans('lang.order_placed_successfully')}}",
                        text: "{{trans('lang.order_has_been_placed')}}",
                    }).then(() => {
                        window.location.reload(); 
                    });
                }
            });
        })
        .catch((error) => {
            console.error("Error placing order:", error);
            Swal.fire({
                icon: 'error',
                title: "{{trans('lang.order_failed')}}",
                text: "{{trans('lang.there_was_issue_placing_order')}}",
            });
        });

        $('#data-table_processing').hide();
    }
    function applyCommission(basePrice) {
        if (!config.isCommissionEnabled || isNaN(basePrice)) {
            return parseFloat(basePrice || 0);
        }
        
        let final = parseFloat(basePrice);
        if (config.commissionType === "percentage") {
            final += final * (config.commissionValue / 100);
        } else {
            final += config.commissionValue;
        }
        return final;
    }
    async function manageInventory(products) {
        
        for (let i = 0; i < products.length; i++) {

            const item = products[i];
            const product_id = item.id;
            const orderQty = parseInt(item.quantity) || 0;

            const variantInfo = item.variant_info || null;

            const productDoc = await database.collection('vendor_products').doc(product_id).get();
            if (!productDoc.exists) continue;

            const productInfo = productDoc.data();
           
            // CASE 1: Variant product (has variant_sku in session)
            if (variantInfo && variantInfo.variant_sku) {
                let variants = productInfo.item_attribute?.variants || [];
                let updatedVariants = variants.map(v => {
                    // match both sides by variant_sku
                    if (v.variant_sku === variantInfo.variant_sku) {
                        let currentQty = (v.variant_quantity !== undefined && v.variant_quantity !== "-1")
                                        ? parseInt(v.variant_quantity)
                                        : null;

                        if (currentQty !== null) {
                            let newQty = currentQty - orderQty;
                            v.variant_quantity = (newQty <= 0 ? 0 : newQty).toString();
                        }
                    }
                    return v;
                });

                await database.collection('vendor_products')
                    .doc(product_id)
                    .update({
                        "item_attribute.variants": updatedVariants
                    });

            }
            // CASE 2: Simple product (no variant)
           else {

                if (productInfo.quantity !== undefined && productInfo.quantity !== -1) {

                    let currentQty = parseInt(productInfo.quantity);
                    let newQty = currentQty - orderQty;
                    if (newQty < 0) newQty = 0;

                    await database.collection('vendor_products')
                        .doc(product_id)
                        .update({
                            quantity: newQty
                        });
                }
            }
        }
    }

    async function walletTransaction(products, cart, id_order, orderVendorID, vendorDetails) {

        let order_subtotal = 0;
        let total_discount = 0;
        //  Calculate subtotal and product extras
        for (let i = 0; i < products.length; i++) {
            let product = products[i];
            let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
            let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
            order_subtotal += itemGross;
        }

        // Calculate item-level taxes (if product-level)
        var taxScope = cart.taxScope ? cart.taxScope : 'order';
        var taxSetting = cart.taxSetting ? cart.taxSetting : [];
        var packagingTax = (cart.taxesByScope && cart.taxesByScope.packaging) ? cart.taxesByScope.packaging : [];
        
        if (taxScope === "product") {
            let itemSubtotal = order_subtotal;
            let itemCombinedTax = 0;
            products.forEach(product => {
                let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
                let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
                let itemDiscount = (itemSubtotal > 0) ? (itemGross / itemSubtotal) * total_discount : 0;
                let itemTaxable = Math.max(0, itemGross - itemDiscount);
                let itemTaxes = product.taxSetting || [];
                itemTaxes.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if (tax.type === "percentage") {
                            taxAmount = (tax.tax / 100) * itemTaxable;
                        } else {
                            taxAmount = tax.tax;
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            });
        } 

        // Order-level taxes (if order-level)
        if (taxScope === "order") {
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
            let orderCombinedTax = 0;
            taxSetting.forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if (tax.type === "percentage") {
                        taxAmount = (tax.tax / 100) * orderTaxable;
                    } else {
                        taxAmount = tax.tax;
                    }
                    total_tax_amount += parseFloat(taxAmount);
                }
            });
        }

        orderTaxAmount = total_tax_amount

        // packaging taxes
        let extraCharges = [
            {key: 'packaging', amount: packagingCharge, taxes: packagingTax || []},
        ];

        extraCharges.forEach(scope => {
            scope.taxes?.forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if (tax.type === "percentage") {
                        taxAmount = (tax.tax / 100) * scope.amount;
                    } else {
                        taxAmount = tax.tax;
                    }
                    orderTaxAmount += parseFloat(taxAmount);
                }
            });
        });
        
        var priceWithCommision = order_subtotal;
        var adminCommHtml = "";
        if (config.commissionType == "percentage") {
            basePrice = (priceWithCommision / (1 + (parseFloat(config.commissionValue) / 100)));
            adminCommission = parseFloat(priceWithCommision - basePrice);
        } else {
            basePrice = priceWithCommision - config.commissionValue;
            adminCommission = parseFloat(priceWithCommision - basePrice);
        }
        
        orderBasePrice = (basePrice - total_discount ) + parseFloat(packagingCharge);

        var wId = database.collection('temp').doc().id;

        let vendorAuthor = vendorDetails.author;

        await database.collection('wallet').doc(wId).set({
            'amount': parseFloat(orderBasePrice).toFixed(config.decimal_degits),
            'date': firebase.firestore.FieldValue.serverTimestamp(),
            'id': wId,
            'isTopUp': true,
            'order_id': id_order,
            'payment_method': 'Wallet',
            'payment_status': 'success',
            'transactionUser': 'vendor',
            'note': 'Order amount credited',
            'user_id': vendorAuthor
        });

        var vendorAmount = orderBasePrice;

        if (orderTaxAmount != 0 && orderTaxAmount != '') {
            var wId2 = database.collection('temp').doc().id;
            await database.collection('wallet').doc(wId2).set({
                'amount': parseFloat(orderTaxAmount).toFixed(config.decimal_degits),
                'date': firebase.firestore.FieldValue.serverTimestamp(),
                'id': wId2,
                'isTopUp': true,
                'order_id': id_order,
                'payment_method': 'tax',
                'payment_status': 'success',
                'transactionUser': 'vendor',
                'user_id': vendorAuthor,
                'note': 'Order tax credited'
            });
        }

        const snapshotsnew = await database.collection('users').where('id', '==', vendorAuthor).get();

        var vendordata = snapshotsnew.docs[0].data();

        if (vendordata) {
            if (parseInt(subscriptionTotalOrders) != -1) {
                subscriptionTotalOrders = parseInt(subscriptionTotalOrders) - 1;
                await database.collection('vendors')
                    .doc(vendorAuthor)
                    .update({
                        'subscriptionTotalOrders': subscriptionTotalOrders.toString()
                    });                
            }
            var vendorWallet = isNaN(vendordata.wallet_amount) || vendordata.wallet_amount == undefined
                    ? 0
                    : parseFloat(vendordata.wallet_amount);
            var newVendorWallet = vendorWallet + vendorAmount + parseFloat(orderTaxAmount);
            await database.collection('users')
                    .doc(vendorAuthor)
                    .update({
                        'wallet_amount': parseFloat(newVendorWallet).toFixed(config.decimal_degits)
                    });
        }
    }
    
    // ========== EXPORT FUNCTIONS FOR HTML ONCLICK ==========
    window.loadProducts = loadProducts;

</script>
@endsection
