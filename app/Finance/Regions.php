<?php

namespace App\Finance;

use App\Models\AdminUserRegion;
use Illuminate\Support\Facades\Auth;

/**
 * Finance is region based. A region takes part when Region Management has
 * "Finance" ticked (regions/{id}.financeEnabled). Its currency is the one set
 * on the region (regions/{id}.currencyId -> currencies/{id}.code), never typed
 * again, and its payment methods are those whose gateway lists the region
 * (settings/*.regionIds).
 *
 * A region-bound admin only ever sees the regions they are assigned to.
 */
class Regions
{
    /** The Payment Methods screen's name for each gateway settings document. */
    public const GATEWAY_NAMES = [
        'stripeSettings' => 'Stripe', 'paypalSettings' => 'PayPal', 'flutterWave' => 'FlutterWave',
        'payStack' => 'Paystack', 'orange_money_settings' => 'OrangePay', 'MercadoPago' => 'MercadoPago',
        'xendit_settings' => 'Xendit', 'midtrans_settings' => 'MidTrans', 'payFastSettings' => 'Payfast',
        'razorpaySettings' => 'Razorpay',
    ];

    /** Payment method family => the gateway settings documents that provide it (spec 4.5). */
    public const METHOD_GATEWAYS = [
        'mobile_money' => ['orange_money_settings', 'flutterWave', 'payStack'],
        'wave' => ['flutterWave'],
        'card' => ['stripeSettings', 'flutterWave', 'payStack', 'MercadoPago', 'xendit_settings', 'midtrans_settings', 'payFastSettings', 'razorpaySettings'],
        'paypal' => ['paypalSettings'],
    ];

    private $db;
    private $regions;
    private $currencies;
    private $gateways;

    public function __construct(Firestore $db)
    {
        $this->db = $db;
    }

    /** Every region: id => data (+ currency code as 'currency'). */
    public function all()
    {
        if ($this->regions === null) {
            $currencies = $this->currencies();
            $this->regions = [];
            foreach ($this->db->query('regions') as $r) {
                $d = $r['data'];
                $d['id'] = $r['id'];
                $d['currency'] = $currencies[$d['currencyId'] ?? '']['code'] ?? '';
                $this->regions[$r['id']] = $d;
            }
            uasort($this->regions, function ($a, $b) {
                return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            });
        }
        return $this->regions;
    }

    /** Region ids this admin may work in; [] = all (unbound admin). */
    public static function allowedIds()
    {
        $user = Auth::user();
        if (!$user || AdminUserRegion::isSuperAdmin($user->id)) {
            return [];
        }
        return AdminUserRegion::regionsOfUser($user->id);
    }

    /** The regions this admin may see, optionally only the finance-enabled ones. */
    public function visible($financeOnly = true)
    {
        $allowed = self::allowedIds();
        return array_filter($this->all(), function ($r) use ($allowed, $financeOnly) {
            if ($financeOnly && empty($r['financeEnabled'])) {
                return false;
            }
            return !$allowed || in_array($r['id'], $allowed, true);
        });
    }

    /** Keeps only ids of finance-enabled regions this admin may use. */
    public function filterIds(array $ids)
    {
        $visible = $this->visible(true);
        return array_values(array_filter(array_unique($ids), function ($id) use ($visible) {
            return is_string($id) && isset($visible[$id]);
        }));
    }

    /** currencies collection: id => data. */
    public function currencies()
    {
        if ($this->currencies === null) {
            $this->currencies = [];
            foreach ($this->db->query('currencies') as $c) {
                $this->currencies[$c['id']] = $c['data'];
            }
        }
        return $this->currencies;
    }

    /** Every currency code known to Region Management / Currencies, sorted. */
    public function currencyCodes()
    {
        $codes = [];
        foreach ($this->currencies() as $c) {
            if (Money::isCurrency($c['code'] ?? null)) {
                $codes[$c['code']] = ($c['name'] ?? '') !== '' ? $c['code'] . ' - ' . $c['name'] : $c['code'];
            }
        }
        ksort($codes);
        return $codes;
    }

    /** code => symbol from the Currencies list (FCFA, ₹, €); the code when it has none. */
    public function currencySymbols()
    {
        $out = [];
        foreach ($this->currencies() as $c) {
            if (Money::isCurrency($c['code'] ?? null)) {
                $out[$c['code']] = trim((string)($c['symbol'] ?? '')) !== '' ? trim($c['symbol']) : $c['code'];
            }
        }
        return $out;
    }

    /** Currency codes of finance-enabled regions (the ones that need a platform account). */
    public function financeCurrencies()
    {
        $codes = [];
        foreach ($this->visible(true) as $r) {
            if (Money::isCurrency($r['currency'])) {
                $codes[] = $r['currency'];
            }
        }
        $codes = array_values(array_unique($codes));
        sort($codes);
        return $codes;
    }

    private function gateways()
    {
        if ($this->gateways === null) {
            $names = array_values(array_unique(array_merge(...array_values(self::METHOD_GATEWAYS))));
            $this->gateways = [];
            foreach ($this->db->getMany(array_map(function ($n) { return 'settings/' . $n; }, $names)) as $path => $doc) {
                $this->gateways[substr($path, strlen('settings/'))] = $doc ? $doc['data'] : null;
            }
        }
        return $this->gateways;
    }

    /**
     * Settings > Payment Methods: the gateway is switched on (its field name
     * varies by gateway) and "Available in Regions" lists the region - an
     * empty list means available everywhere, as that screen says.
     */
    private function gatewayServes($name, $regionId)
    {
        $g = $this->gateways()[$name] ?? null;
        if (!$g) {
            return false;
        }
        $on = ($g['isEnabled'] ?? null) === true || ($g['isEnable'] ?? null) === true || ($g['enable'] ?? null) === true;
        $regionIds = array_values(array_filter((array)($g['regionIds'] ?? [])));
        return $on && (!$regionIds || in_array($regionId, $regionIds, true));
    }

    /** method => names of the gateways that provide it in the region. */
    public function providersFor($regionId)
    {
        $out = [];
        foreach (self::METHOD_GATEWAYS as $method => $gateways) {
            foreach ($gateways as $g) {
                if ($this->gatewayServes($g, $regionId)) {
                    $out[$method][] = self::GATEWAY_NAMES[$g] ?? $g;
                }
            }
        }
        return $out;
    }

    /**
     * Methods a region can offer: the Spideli Finance wallet and bank payouts
     * always (no gateway involved), the others when one of their gateways
     * serves the region.
     */
    public function methodsFor($regionId)
    {
        return array_merge(['wallet'], array_keys($this->providersFor($regionId)), ['bank']);
    }

    /** method => [names of the given regions that cannot offer it]. */
    public function methodGaps(array $regionIds)
    {
        $gaps = [];
        foreach ($regionIds as $id) {
            $have = $this->methodsFor($id);
            foreach (array_keys(self::METHOD_GATEWAYS) as $m) {
                if (!in_array($m, $have, true)) {
                    $gaps[$m][] = $this->all()[$id]['name'] ?? $id;
                }
            }
        }
        return $gaps;
    }

    /** Country codes of the regions (ISO-2), for the country dropdowns. */
    public function countryCodes()
    {
        $codes = [];
        foreach ($this->all() as $r) {
            if (preg_match('/^[A-Z]{2}$/', $r['countryCode'] ?? '')) {
                $codes[] = $r['countryCode'];
            }
        }
        $codes = array_values(array_unique($codes));
        sort($codes);
        return $codes;
    }

    public function name($regionId)
    {
        return $this->all()[$regionId]['name'] ?? $regionId;
    }

    public function currencyOf($regionId)
    {
        return $this->all()[$regionId]['currency'] ?? '';
    }

    /** The Firestore id of a module's settings for a region. */
    public static function docId($regionId, $module)
    {
        return $regionId . '_' . $module;
    }
}
