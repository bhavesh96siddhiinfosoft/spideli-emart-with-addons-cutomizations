{{--
    The tab row of Settings > Payment Methods, shared by every payment page
    (#92, 16 Nov 2026). Add a gateway here once and every page shows it.

      @include('settings.app.partials.payment_tabs', ['activeTab' => 'stripe'])

    Each tab: its key (activeTab), page URL, label, the class its page uses for
    the "Active" badge, and its settings/{doc} document. A tab is "Active" when
    its document has isEnabled, isEnable or enable set to true (the gateways
    were written at different times and name the flag differently).
--}}
@php
    $paymentTabs = [
        ['key' => 'stripe', 'url' => 'settings/payment/stripe', 'label' => trans('lang.app_setting_stripe'), 'class' => 'stripe_active_label', 'doc' => 'stripeSettings'],
        ['key' => 'cod', 'url' => 'settings/payment/cod', 'label' => trans('lang.app_setting_cod_short'), 'class' => 'cod_active_label', 'doc' => 'CODSettings'],
        ['key' => 'razorpay', 'url' => 'settings/payment/razorpay', 'label' => trans('lang.app_setting_razorpay'), 'class' => 'razorpay_active_label', 'doc' => 'razorpaySettings'],
        ['key' => 'paypal', 'url' => 'settings/payment/paypal', 'label' => trans('lang.app_setting_paypal'), 'class' => 'paypal_active_label', 'doc' => 'paypalSettings'],
        ['key' => 'wallet', 'url' => 'settings/payment/wallet', 'label' => trans('lang.app_setting_wallet'), 'class' => 'wallet_active_label', 'doc' => 'walletSettings'],
        ['key' => 'payfast', 'url' => 'settings/payment/payfast', 'label' => trans('lang.payfast'), 'class' => 'payfast_active_label', 'doc' => 'payFastSettings'],
        ['key' => 'paystack', 'url' => 'settings/payment/paystack', 'label' => trans('lang.app_setting_paystack_lable'), 'class' => 'paystack_active_label', 'doc' => 'payStack'],
        ['key' => 'flutterwave', 'url' => 'settings/payment/flutterwave', 'label' => trans('lang.flutterWave'), 'class' => 'flutterWave_active_label', 'doc' => 'flutterWave'],
        ['key' => 'mercadopago', 'url' => 'settings/payment/mercadopago', 'label' => trans('lang.mercadopago'), 'class' => 'mercadopago_active_label', 'doc' => 'MercadoPago'],
        ['key' => 'xendit', 'url' => 'settings/payment/xendit', 'label' => trans('lang.xendit'), 'class' => 'xendit_active_label', 'doc' => 'xendit_settings'],
        ['key' => 'midtrans', 'url' => 'settings/payment/midtrans', 'label' => trans('lang.midtrans'), 'class' => 'midTrans_active_label', 'doc' => 'midtrans_settings'],
        ['key' => 'orangepay', 'url' => 'settings/payment/orangepay', 'label' => trans('lang.orangePay'), 'class' => 'orangePay_active_label', 'doc' => 'orange_money_settings'],
        // #92: the client's gateways
        ['key' => 'mycoolpay', 'url' => 'settings/payment/mycoolpay', 'label' => trans('lang.mycoolpay'), 'class' => 'mycoolpay_active_label', 'doc' => 'mycoolpay_settings'],
        ['key' => 'mypvit', 'url' => 'settings/payment/mypvit', 'label' => trans('lang.mypvit'), 'class' => 'mypvit_active_label', 'doc' => 'mypvit_settings'],
        ['key' => 'sebpay_cm', 'url' => 'settings/payment/sebpay-cm', 'label' => trans('lang.sebpay_cm'), 'class' => 'sebpay_cm_active_label', 'doc' => 'sebpay_cm_settings'],
        ['key' => 'sebpay_gn', 'url' => 'settings/payment/sebpay-gn', 'label' => trans('lang.sebpay_gn'), 'class' => 'sebpay_gn_active_label', 'doc' => 'sebpay_gn_settings'],
        ['key' => 'pispi', 'url' => 'settings/payment/pispi', 'label' => trans('lang.pispi'), 'class' => 'pispi_active_label', 'doc' => 'pispi_settings'],
    ];
    $activeTab = $activeTab ?? '';
@endphp
<ul class="nav nav-tabs card-header-tabs align-items-end">
    @foreach ($paymentTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === $tab['key'] ? 'active' : '' }} {{ $tab['class'] }}" href="{!! url($tab['url']) !!}"><i
                    class="fa fa-envelope-o mr-2"></i>{{ $tab['label'] }}<span class="badge ml-2"></span>
            </a>
        </li>
    @endforeach
</ul>
<script>
    /* The "Active" badges, for every tab. Runs after the page's own scripts
     * (window load), when firebase is ready. */
    window.addEventListener('load', function () {
        if (typeof firebase === 'undefined' || typeof jQuery === 'undefined') {
            return;
        }
        var tabs = {!! json_encode(array_map(function ($t) { return ['doc' => $t['doc'], 'class' => $t['class']]; }, $paymentTabs)) !!};
        var db = firebase.firestore();
        tabs.forEach(function (t) {
            db.collection('settings').doc(t.doc).get().then(function (snap) {
                var d = snap.exists ? snap.data() : null;
                if (d && (d.isEnabled === true || d.isEnable === true || d.enable === true)) {
                    jQuery('.' + t.class + ' span').addClass('badge-success').text('Active');
                }
            }).catch(function () {});
        });
    });
</script>
