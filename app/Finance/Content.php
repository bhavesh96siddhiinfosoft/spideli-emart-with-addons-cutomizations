<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase D3b (spec 6.13, 3.12, 3.4): the content customers read - economic
 * indicators (+ history), exchange rates, market securities (+ price
 * history), IPOs, stock collections, news and bill billers.
 *
 * Each kind is described once in SCHEMAS (collection, fields, list
 * columns); one form and one list serve them all. Entered in the panel or
 * imported from CSV (chosen 16 Oct 2026); there is no data feed yet.
 *
 * Exchange rates are append-only snapshots. A pair marked fixed (an
 * official peg) is kept in the staff-only exchange_rate_fixed/{pair} and
 * re-posted every morning by the daily job, because the app refuses a
 * conversion with a rate older than convert.maxRateAgeHours.
 */
class Content
{
    /*
     * Field types: text, textarea, url (https), number (float), int, money
     * (minor units of the record's "currency"), date, datetime, bool,
     * select (options), multiselect (options from another kind), regex,
     * packages (rows of code / name / amount). "req" = required.
     */
    public const SCHEMAS = [
        'indicators' => [
            'collection' => 'economic_indicators', 'title' => 'Economic indicators', 'label' => 'name',
            'columns' => ['name', 'country', 'value', 'period', 'source'],
            'help' => 'Approved sources only (central banks, statistics institutes, IMF / World Bank); never estimated. A new value moves the old one to "previous" and is kept in the history.',
            'fields' => [
                'country' => ['text', 'Country (ISO-2)', 'req' => true, 'max' => 2],
                'code' => ['text', 'Code', 'req' => true, 'help' => 'e.g. GDP_GROWTH, INFLATION'],
                'name' => ['text', 'Name', 'req' => true],
                'value' => ['number', 'Value', 'req' => true],
                'unit' => ['text', 'Unit', 'help' => 'e.g. %, bn'],
                'currency' => ['text', 'Currency (ISO-3)', 'max' => 3],
                'period' => ['text', 'Period', 'req' => true, 'help' => 'e.g. 2026-Q2, 2025'],
                'periodEnd' => ['date', 'Period end'],
                'source' => ['text', 'Source', 'req' => true],
                'sourceUrl' => ['url', 'Source link'],
                'order' => ['int', 'Display order'],
                'published' => ['bool', 'Published'],
            ],
        ],
        'securities' => [
            'collection' => 'market_securities', 'title' => 'Securities', 'label' => 'symbol',
            'columns' => ['symbol', 'name', 'type', 'exchange', 'price', 'changePercent'],
            'help' => 'Prices in major units, from a licensed market-data source. Each new price is kept in the price history.',
            'fields' => [
                'type' => ['select', 'Type', 'req' => true, 'options' => ['stock' => 'Stock', 'etf' => 'ETF', 'bond' => 'Bond', 'index' => 'Index']],
                'symbol' => ['text', 'Symbol', 'req' => true],
                'name' => ['text', 'Name', 'req' => true],
                'exchange' => ['text', 'Exchange', 'help' => 'e.g. BVMAC, BRVM'],
                'country' => ['text', 'Country (ISO-2)', 'max' => 2],
                'currency' => ['text', 'Currency (ISO-3)', 'req' => true, 'max' => 3],
                'price' => ['number', 'Price', 'req' => true],
                'change' => ['number', 'Change', 'help' => 'Blank = computed from the previous price'],
                'changePercent' => ['number', 'Change %', 'help' => 'Blank = computed'],
                'open' => ['number', 'Open'],
                'high' => ['number', 'High'],
                'low' => ['number', 'Low'],
                'volume' => ['int', 'Volume'],
                'sector' => ['text', 'Sector'],
                'couponPercent' => ['number', 'Coupon % (bonds)'],
                'maturityDate' => ['date', 'Maturity (bonds)'],
                'yieldPercent' => ['number', 'Yield % (bonds)'],
                'source' => ['text', 'Source', 'req' => true],
                'delayed' => ['bool', 'Delayed prices'],
                'published' => ['bool', 'Published'],
            ],
        ],
        'ipos' => [
            'collection' => 'ipo_listings', 'title' => 'IPOs', 'label' => 'company',
            'columns' => ['company', 'exchange', 'status', 'openDate', 'closeDate'],
            'help' => 'Information only: customers cannot subscribe in Spideli.',
            'fields' => [
                'company' => ['text', 'Company', 'req' => true],
                'symbol' => ['text', 'Symbol'],
                'exchange' => ['text', 'Exchange'],
                'country' => ['text', 'Country (ISO-2)', 'max' => 2],
                'sector' => ['text', 'Sector'],
                'currency' => ['text', 'Currency (ISO-3)', 'max' => 3],
                'priceLow' => ['number', 'Price from'],
                'priceHigh' => ['number', 'Price to'],
                'offerPrice' => ['number', 'Offer price'],
                'openDate' => ['date', 'Subscription opens'],
                'closeDate' => ['date', 'Subscription closes'],
                'listingDate' => ['date', 'Listing date'],
                'status' => ['select', 'Status', 'req' => true, 'options' => ['upcoming' => 'Upcoming', 'open' => 'Open for subscription', 'closed' => 'Subscription closed', 'listed' => 'Listed', 'withdrawn' => 'Withdrawn']],
                'prospectusUrl' => ['url', 'Prospectus link'],
                'source' => ['text', 'Source', 'req' => true],
                'published' => ['bool', 'Published'],
            ],
        ],
        'collections' => [
            'collection' => 'stock_collections', 'title' => 'Stock collections', 'label' => 'title',
            'columns' => ['title', 'theme', 'riskLevel'],
            'help' => 'A themed selection, shown "for information, not a recommendation".',
            'fields' => [
                'title' => ['text', 'Title', 'req' => true],
                'summary' => ['textarea', 'Summary'],
                'theme' => ['text', 'Theme'],
                'securityIds' => ['multiselect', 'Securities', 'from' => 'securities', 'req' => true],
                'riskLevel' => ['int', 'Risk level (1-7)', 'min' => 1, 'max' => 7],
                'order' => ['int', 'Display order'],
                'published' => ['bool', 'Published'],
            ],
        ],
        'news' => [
            'collection' => 'finance_news', 'title' => 'News', 'label' => 'title',
            'columns' => ['title', 'source', 'category', 'publishedAt'],
            'help' => 'A link to the full article on its publisher\'s site (https).',
            'fields' => [
                'title' => ['text', 'Title', 'req' => true],
                'summary' => ['textarea', 'Summary'],
                'source' => ['text', 'Publisher', 'req' => true],
                'url' => ['url', 'Article link', 'req' => true],
                'category' => ['select', 'Category', 'req' => true, 'options' => ['news' => 'News', 'analysis' => 'Analysis', 'markets' => 'Markets', 'economy' => 'Economy', 'companies' => 'Companies']],
                'country' => ['text', 'Country (ISO-2)', 'max' => 2],
                'publishedAt' => ['datetime', 'Published on', 'req' => true],
                'published' => ['bool', 'Published'],
            ],
        ],
        'billers' => [
            'collection' => 'utility_billers', 'title' => 'Billers', 'label' => 'name',
            'columns' => ['name', 'category', 'country', 'amountType'],
            'help' => 'Only billers the operations team can pay. Test the account pattern on real samples before publishing.',
            'fields' => [
                'name' => ['text', 'Name', 'req' => true],
                'category' => ['select', 'Category', 'req' => true, 'options' => ['electricity' => 'Electricity', 'water' => 'Water', 'tv' => 'TV', 'internet' => 'Internet', 'phone' => 'Phone',
                    'gas' => 'Gas', 'education' => 'Education', 'taxes' => 'Taxes', 'products' => 'Products', 'other' => 'Other']],
                'country' => ['text', 'Country (ISO-2)', 'req' => true, 'max' => 2],
                'currency' => ['text', 'Currency (ISO-3)', 'req' => true, 'max' => 3],
                'accountLabel' => ['text', 'Account label', 'req' => true, 'help' => 'e.g. Meter number'],
                'accountHint' => ['text', 'Account hint'],
                'accountPattern' => ['regex', 'Account pattern', 'help' => 'A regular expression the whole account must match, e.g. \d{11}'],
                'amountType' => ['select', 'Amount', 'req' => true, 'options' => ['open' => 'Any amount (open)', 'due' => 'The amount due (lookup)', 'packages' => 'Fixed packages']],
                'packages' => ['packages', 'Packages'],
                'lookup' => ['bool', 'Look the account up'],
                'partialPayment' => ['bool', 'Partial payment allowed'],
                'min' => ['money', 'Minimum'],
                'max' => ['money', 'Maximum'],
                'feePercent' => ['number', 'Fee %'],
                'feeFixed' => ['money', 'Fixed fee'],
                'feeMax' => ['money', 'Fee cap'],
                'order' => ['int', 'Display order'],
                'published' => ['bool', 'Published'],
            ],
        ],
    ];

    public const RATE_KINDS = ['official', 'indicative'];

    private $db;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
    }

    private function admin()
    {
        return 'admin:' . Auth::id();
    }

    public static function schema($kind)
    {
        if (!isset(self::SCHEMAS[$kind])) {
            throw new FinanceException('invalid', 'Unknown content.');
        }
        return self::SCHEMAS[$kind];
    }

    private static function tz()
    {
        return new \DateTimeZone(config('app.timezone'));
    }

    /* ============================================================ values */

    /**
     * One field from a form or CSV value. Returns [present, value]; a blank
     * optional field is not present (deleted on save). Throws on bad input.
     */
    public static function field($key, array $f, $raw, array $record)
    {
        $label = __($f[1]);
        $type = $f[0];
        if ($type === 'bool') {
            return [true, !empty($raw) && !in_array(strtolower((string)$raw), ['0', 'false', 'no'], true)];
        }
        if ($type === 'multiselect') {
            $v = array_values(array_unique(array_filter(array_map('strval', (array)$raw), 'strlen')));
            if (!$v && !empty($f['req'])) {
                throw new FinanceException('invalid', "$label: " . __('choose at least one.'));
            }
            return [true, array_slice($v, 0, 100)];
        }
        if ($type === 'packages') {
            $rows = [];
            foreach ((array)$raw as $r) {
                $code = trim((string)($r['code'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $amount = Money::toMinor($r['amount'] ?? '', $record['currency'] ?? 'XAF');
                if (!$amount) {
                    throw new FinanceException('invalid', "$label: " . __('each package needs an amount.'));
                }
                $rows[] = ['code' => $code, 'name' => trim((string)($r['name'] ?? '')) ?: $code, 'amount' => $amount];
            }
            return [true, $rows];
        }
        $raw = is_string($raw) ? trim($raw) : $raw;
        if ($raw === null || $raw === '') {
            if (!empty($f['req'])) {
                throw new FinanceException('invalid', "$label: " . __('required.'));
            }
            return [false, null];
        }
        switch ($type) {
            case 'number':
                $v = str_replace([' ', ','], ['', '.'], (string)$raw);
                if (!is_numeric($v)) {
                    throw new FinanceException('invalid', "$label: " . __('a number.'));
                }
                return [true, (float)$v];
            case 'int':
                if (!preg_match('/^-?\d+$/', str_replace(' ', '', (string)$raw))) {
                    throw new FinanceException('invalid', "$label: " . __('a whole number.'));
                }
                $v = (int)str_replace(' ', '', (string)$raw);
                if ((isset($f['min']) && $v < $f['min']) || (isset($f['max']) && $v > $f['max'])) {
                    throw new FinanceException('invalid', "$label: " . __('out of range.'));
                }
                return [true, $v];
            case 'money':
                return [true, Money::toMinor($raw, $record['currency'] ?? 'XAF')];
            case 'date':
            case 'datetime':
                $d = $type === 'date' ? \DateTimeImmutable::createFromFormat('!Y-m-d', substr((string)$raw, 0, 10), self::tz())
                    : (\DateTimeImmutable::createFromFormat('Y-m-d\TH:i', (string)$raw, self::tz()) ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i', (string)$raw, self::tz()));
                if (!$d) {
                    throw new FinanceException('invalid', "$label: " . __('a date (YYYY-MM-DD).'));
                }
                return [true, $d];
            case 'url':
                if (!preg_match('#^https://[^\s/]+\.[^\s]+$#', (string)$raw)) {
                    throw new FinanceException('invalid', "$label: " . __('a link starting with https://'));
                }
                return [true, (string)$raw];
            case 'regex':
                if (@preg_match('/^(?:' . str_replace('/', '\/', (string)$raw) . ')$/', '') === false) {
                    throw new FinanceException('invalid', "$label: " . __('not a valid pattern.'));
                }
                return [true, (string)$raw];
            case 'select':
                if (!array_key_exists((string)$raw, $f['options'])) {
                    throw new FinanceException('invalid', "$label: " . __('not one of the choices.'));
                }
                return [true, (string)$raw];
            default:
                $v = (string)$raw;
                if (isset($f['max']) && mb_strlen($v) > $f['max']) {
                    throw new FinanceException('invalid', "$label: " . __('too long.'));
                }
                if (in_array($key, ['country'], true)) {
                    $v = strtoupper($v);
                }
                if ($key === 'currency' && !Money::isCurrency(strtoupper($v))) {
                    throw new FinanceException('invalid', "$label: " . __('a 3-letter currency code.'));
                }
                return [true, $key === 'currency' ? strtoupper($v) : mb_substr($v, 0, 5000)];
        }
    }

    /**
     * A record of $kind from input (form or CSV row). Returns the fields to
     * write: blank optional ones as Value::delete() for an existing record.
     */
    public static function fromInput($kind, array $in, array $existing = null)
    {
        $s = self::schema($kind);
        $record = [];
        // The currency first: money fields are in its minor units.
        if (isset($s['fields']['currency'])) {
            [$ok, $cur] = self::field('currency', $s['fields']['currency'], $in['currency'] ?? ($existing['currency'] ?? ''), []);
            if ($ok) {
                $record['currency'] = $cur;
            }
        }
        $out = $record;
        foreach ($s['fields'] as $key => $f) {
            if ($key === 'currency') {
                continue;
            }
            [$ok, $v] = self::field($key, $f, $in[$key] ?? null, $record);
            if ($ok) {
                $out[$key] = $v;
            } elseif ($existing !== null && array_key_exists($key, $existing)) {
                $out[$key] = Value::delete();
            }
        }
        if ($kind === 'billers') {
            if (($out['amountType'] ?? '') === 'packages' && empty($out['packages'])) {
                throw new FinanceException('invalid', 'A biller with fixed packages needs at least one package.');
            }
            if (isset($out['min'], $out['max']) && !($out['min'] instanceof Value) && !($out['max'] instanceof Value) && $out['min'] > $out['max']) {
                throw new FinanceException('invalid', 'The minimum is above the maximum.');
            }
        }
        return $out;
    }

    /** Lines that do / do not match a biller's account pattern: [sample => bool]. */
    public static function testPattern($pattern, $samples)
    {
        $out = [];
        foreach (array_filter(array_map('trim', preg_split('/\r?\n/', (string)$samples)), 'strlen') as $s) {
            $out[$s] = (bool)@preg_match('/^(?:' . str_replace('/', '\/', (string)$pattern) . ')$/', $s);
        }
        return $out;
    }

    /* ============================================================== save */

    /**
     * Creates or edits one record (one transaction with its audit). Hooks:
     * an indicator's new value moves the old one to previousValue and is
     * kept in economic_indicator_history; a security's new price fills
     * change / change % when left blank and is kept in market_price_history.
     */
    public function save($kind, $id, array $data)
    {
        $s = self::schema($kind);
        $col = $s['collection'];
        $isNew = !$id;
        $id = $id ?: Money::random(20);
        return $this->db->transaction(function (Transaction $t) use ($kind, $col, $id, $isNew, $data) {
            $before = null;
            if (!$isNew) {
                $snap = $t->get($col . '/' . $id);
                if (!$snap) {
                    throw new FinanceException('missing', 'This record no longer exists.');
                }
                $before = $snap['data'];
            }
            $extra = $this->hooks($t, $kind, $id, $data, $before);
            $data += $extra;
            $data['updatedAt'] = Value::serverTimestamp();
            if ($isNew) {
                $t->create($col . '/' . $id, array_filter($data, function ($v) { return !($v instanceof Value && $v->isDelete()); }) + ['createdAt' => Value::serverTimestamp()]);
            } else {
                $t->update($col . '/' . $id, $data);
            }
            [$b, $a] = $before ? Audit::diff($before, $data) : [null, $data];
            Audit::add($t, 'content.' . $kind . ($isNew ? '_create' : '_update'), $col, $id, $b, $a);
            return $id;
        });
    }

    /** Hook writes inside the save transaction; returns extra fields for the record. */
    private function hooks(Transaction $t, $kind, $id, array &$data, $before)
    {
        $extra = [];
        if ($kind === 'indicators') {
            $changed = !$before || (float)($before['value'] ?? NAN) !== (float)$data['value'] || ($before['period'] ?? '') !== $data['period'];
            if ($before && $changed && isset($before['value']) && ($before['period'] ?? '') !== $data['period']) {
                $extra['previousValue'] = $before['value'];
            }
            if ($changed) {
                $hid = $id . '_' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $data['period']);
                $t->set('economic_indicator_history/' . $hid, ['indicatorId' => $id, 'period' => $data['period'],
                    'periodEnd' => ($data['periodEnd'] ?? null) instanceof \DateTimeInterface ? $data['periodEnd'] : new \DateTimeImmutable(), 'value' => $data['value']]);
            }
        }
        if ($kind === 'securities') {
            $prev = isset($before['price']) ? (float)$before['price'] : null;
            $price = (float)$data['price'];
            if (!isset($data['change']) || $data['change'] instanceof Value) {
                $data['change'] = $prev !== null ? round($price - $prev, 6) : 0.0;
            }
            if (!isset($data['changePercent']) || $data['changePercent'] instanceof Value) {
                $data['changePercent'] = $prev ? round(($price - $prev) / $prev * 100, 2) : 0.0;
            }
            if ($prev === null || $prev !== $price) {
                $day = (new \DateTimeImmutable('today', self::tz()));
                $t->set('market_price_history/' . $id . '_' . $day->format('Y-m-d'), ['securityId' => $id, 'date' => $day, 'close' => $price]);
            }
        }
        return $extra;
    }

    /* ============================================================= rates */

    public static function pair($from, $to)
    {
        return $from . '_' . $to;
    }

    /**
     * Posts one exchange rate snapshot (1 from = rate to). $fixed marks the
     * pair for the daily re-post (or unmarks it).
     */
    public function addRate($from, $to, $rate, $source, $kind, \DateTimeInterface $observedAt = null, $fixed = null)
    {
        $from = strtoupper(trim((string)$from));
        $to = strtoupper(trim((string)$to));
        if (!Money::isCurrency($from) || !Money::isCurrency($to) || $from === $to) {
            throw new FinanceException('invalid', 'Choose two different currencies.');
        }
        $rate = str_replace([' ', ','], ['', '.'], (string)$rate);
        if (!is_numeric($rate) || (float)$rate <= 0) {
            throw new FinanceException('invalid', 'The rate must be a number above 0.');
        }
        if (trim((string)$source) === '') {
            throw new FinanceException('invalid', 'Name the source of the rate.');
        }
        if (!in_array($kind, self::RATE_KINDS, true)) {
            throw new FinanceException('invalid', 'Choose official or indicative.');
        }
        $observedAt = $observedAt ?: new \DateTimeImmutable();
        if ($observedAt > new \DateTimeImmutable('+10 minutes')) {
            throw new FinanceException('invalid', 'A rate cannot be observed in the future.');
        }
        $snap = ['from' => $from, 'to' => $to, 'pair' => self::pair($from, $to), 'rate' => (float)$rate, 'source' => trim($source), 'kind' => $kind,
            'observedAt' => \DateTimeImmutable::createFromInterface($observedAt), 'createdBy' => $this->admin()];
        $id = Money::random(20);
        $writes = [$this->db->write('create', 'exchange_rate_snapshots/' . $id, $snap)];
        if ($fixed === true) {
            $writes[] = $this->db->write('set', 'exchange_rate_fixed/' . self::pair($from, $to), ['from' => $from, 'to' => $to, 'rate' => (float)$rate,
                'source' => trim($source), 'kind' => $kind, 'updatedAt' => Value::serverTimestamp(), 'updatedBy' => $this->admin()]);
        }
        $writes[] = Audit::write($this->db, 'content.rate_add', 'exchange_rate_snapshots', $id, null, $snap + ['fixed' => $fixed]);
        $this->db->batch($writes);
        return $id;
    }

    public function unfix($pair)
    {
        $this->db->batch([
            $this->db->write('update', 'exchange_rate_fixed/' . $pair, ['active' => false, 'updatedAt' => Value::serverTimestamp()]),
            Audit::write($this->db, 'content.rate_unfix', 'exchange_rate_fixed', $pair, ['active' => true], ['active' => false]),
        ]);
    }

    /** The daily re-post of every fixed pair (a new snapshot observed now). Returns the pairs posted. */
    public function repostFixed()
    {
        $writes = [];
        $pairs = [];
        foreach ($this->db->query('exchange_rate_fixed', [], [], 200) as $f) {
            $d = $f['data'];
            if (($d['active'] ?? true) === false || (float)($d['rate'] ?? 0) <= 0) {
                continue;
            }
            $writes[] = $this->db->write('create', 'exchange_rate_snapshots/' . Money::random(20), ['from' => $d['from'], 'to' => $d['to'], 'pair' => $f['id'],
                'rate' => (float)$d['rate'], 'source' => $d['source'] ?? '', 'kind' => $d['kind'] ?? 'official', 'observedAt' => new \DateTimeImmutable(), 'createdBy' => 'job:fixed-rate']);
            $pairs[] = $f['id'];
        }
        if ($writes) {
            $writes[] = Audit::write($this->db, 'job.rate_repost', 'exchange_rate_fixed', implode(',', $pairs), null, ['pairs' => $pairs]);
            $this->db->batch($writes);
        }
        return $pairs;
    }

    /** The newest snapshot of each pair: [pair => data + id]. */
    public function latestRates()
    {
        $out = [];
        foreach ($this->db->query('exchange_rate_snapshots', [], [['observedAt', 'desc']], 2000) as $s) {
            $p = $s['data']['pair'] ?? '';
            $at = $s['data']['observedAt'] ?? null;
            if (!isset($out[$p]) || ($at instanceof \DateTimeInterface && $at > $out[$p]['observedAt'])) {
                $out[$p] = $s['data'] + ['id' => $s['id']];
            }
        }
        ksort($out);
        return $out;
    }

    /* ============================================================ import */

    public const IMPORTS = [
        'indicators' => ['country', 'code', 'name', 'value', 'unit', 'currency', 'period', 'periodEnd', 'source', 'sourceUrl'],
        'rates' => ['from', 'to', 'rate', 'source', 'kind', 'observedAt'],
        'prices' => ['symbol', 'date', 'close'],
    ];

    /** Reads a CSV with a header row: [line => [column => value]]; separators "," or ";". */
    public static function readCsv($path)
    {
        $text = (string)file_get_contents($path);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $lines = preg_split('/\r\n|\n|\r/', trim($text));
        if (count($lines) < 2) {
            throw new FinanceException('invalid', 'The file needs a header row and at least one row.');
        }
        $sep = substr_count($lines[0], ';') > substr_count($lines[0], ',') ? ';' : ',';
        $head = array_map(function ($h) { return trim($h); }, str_getcsv($lines[0], $sep));
        $rows = [];
        foreach (array_slice($lines, 1) as $i => $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = str_getcsv($line, $sep);
            $rows[$i + 2] = array_combine($head, array_pad(array_slice($cells, 0, count($head)), count($head), ''));
        }
        if (count($rows) > 2000) {
            throw new FinanceException('invalid', 'At most 2 000 rows per file.');
        }
        return [$head, $rows];
    }

    /**
     * Imports a CSV: every row is checked first, and nothing is written when
     * one is wrong (the errors are listed). Then written in batches with an
     * audit entry each. Returns ['written' => n, 'created' => n].
     *  - indicators: one per country + code (updated, else created
     *    unpublished), its value kept in the history;
     *  - rates: one snapshot per row;
     *  - prices: one history close per symbol + date; the newest date
     *    becomes the security's price.
     */
    public function import($what, $path)
    {
        if (!isset(self::IMPORTS[$what])) {
            throw new FinanceException('invalid', 'Unknown import.');
        }
        [$head, $rows] = self::readCsv($path);
        $need = $what === 'indicators' ? ['country', 'code', 'name', 'value', 'period', 'source'] : ($what === 'rates' ? ['from', 'to', 'rate', 'source'] : self::IMPORTS['prices']);
        $missing = array_diff($need, $head);
        if ($missing) {
            throw new FinanceException('invalid', __('Missing columns:') . ' ' . implode(', ', $missing) . '. ' . __('Expected:') . ' ' . implode(', ', self::IMPORTS[$what]));
        }
        $errors = [];
        $writes = [];
        $created = 0;
        $now = new \DateTimeImmutable();
        if ($what === 'indicators') {
            $existing = [];
            foreach ($this->db->query('economic_indicators', [], [], 5000) as $d) {
                $existing[strtoupper($d['data']['country'] ?? '') . '|' . ($d['data']['code'] ?? '')] = $d;
            }
            foreach ($rows as $line => $r) {
                try {
                    $key = strtoupper(trim($r['country'])) . '|' . trim($r['code']);
                    $old = $existing[$key] ?? null;
                    $rec = self::fromInput('indicators', $r + ['published' => $old['data']['published'] ?? false, 'order' => $old['data']['order'] ?? 0], $old ? $old['data'] : null);
                    $id = $old ? $old['id'] : Money::random(20);
                    if ($old && isset($old['data']['value']) && ($old['data']['period'] ?? '') !== $rec['period']) {
                        $rec['previousValue'] = $old['data']['value'];
                    }
                    $rec['updatedAt'] = Value::serverTimestamp();
                    $writes[] = $this->db->write($old ? 'update' : 'create', 'economic_indicators/' . $id,
                        $old ? $rec : array_filter($rec, function ($v) { return !($v instanceof Value && $v->isDelete()); }) + ['createdAt' => Value::serverTimestamp()]);
                    $writes[] = $this->db->write('set', 'economic_indicator_history/' . $id . '_' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $rec['period']),
                        ['indicatorId' => $id, 'period' => $rec['period'], 'periodEnd' => $rec['periodEnd'] ?? $now, 'value' => $rec['value']]);
                    $existing[$key] = ['id' => $id, 'data' => array_filter($rec, function ($v) { return !($v instanceof Value); })];
                    $created += $old ? 0 : 1;
                } catch (FinanceException $e) {
                    $errors[] = __('Line') . " $line: " . __($e->getMessage());
                }
            }
        } elseif ($what === 'rates') {
            foreach ($rows as $line => $r) {
                try {
                    $from = strtoupper(trim($r['from']));
                    $to = strtoupper(trim($r['to']));
                    $rate = str_replace([' ', ','], ['', '.'], trim($r['rate']));
                    $kind = trim($r['kind'] ?? '') ?: 'indicative';
                    if (!Money::isCurrency($from) || !Money::isCurrency($to) || $from === $to || !is_numeric($rate) || (float)$rate <= 0 || trim($r['source']) === '' || !in_array($kind, self::RATE_KINDS, true)) {
                        throw new FinanceException('invalid', 'from, to (two currencies), rate > 0, source and kind (official / indicative) are required.');
                    }
                    $at = trim($r['observedAt'] ?? '') === '' ? $now : self::field('observedAt', ['datetime', 'Observed'], str_replace('T', ' ', substr(trim($r['observedAt']), 0, 16)), [])[1];
                    if ($at > new \DateTimeImmutable('+10 minutes')) {
                        throw new FinanceException('invalid', 'A rate cannot be observed in the future.');
                    }
                    $writes[] = $this->db->write('create', 'exchange_rate_snapshots/' . Money::random(20), ['from' => $from, 'to' => $to, 'pair' => self::pair($from, $to),
                        'rate' => (float)$rate, 'source' => trim($r['source']), 'kind' => $kind, 'observedAt' => $at, 'createdBy' => $this->admin()]);
                    $created++;
                } catch (FinanceException $e) {
                    $errors[] = __('Line') . " $line: " . __($e->getMessage());
                }
            }
        } else {
            $bySymbol = [];
            $priceOf = [];
            foreach ($this->db->query('market_securities', [], [], 5000) as $d) {
                $bySymbol[strtoupper($d['data']['symbol'] ?? '')] = $d;
                $priceOf[$d['id']] = (float)($d['data']['price'] ?? 0);
            }
            $closes = [];   // securityId => [Y-m-d => close]
            foreach ($rows as $line => $r) {
                try {
                    $sec = $bySymbol[strtoupper(trim($r['symbol']))] ?? null;
                    if (!$sec) {
                        throw new FinanceException('invalid', 'Unknown symbol: add the security first.');
                    }
                    [, $day] = self::field('date', ['date', 'Date', 'req' => true], $r['date'], []);
                    [, $close] = self::field('close', ['number', 'Close', 'req' => true], $r['close'], []);
                    if ($close <= 0) {
                        throw new FinanceException('invalid', 'The close must be above 0.');
                    }
                    $writes[] = $this->db->write('set', 'market_price_history/' . $sec['id'] . '_' . $day->format('Y-m-d'), ['securityId' => $sec['id'], 'date' => $day, 'close' => $close]);
                    $closes[$sec['id']][$day->format('Y-m-d')] = $close;
                    $created++;
                } catch (FinanceException $e) {
                    $errors[] = __('Line') . " $line: " . __($e->getMessage());
                }
            }
            // The newest close becomes the price; the change is against the day before it in
            // the file, else against the price the security had.
            foreach ($closes as $sid => $byDay) {
                ksort($byDay);
                $close = end($byDay);
                $prev = count($byDay) > 1 ? array_values($byDay)[count($byDay) - 2] : $priceOf[$sid];
                $writes[] = $this->db->write('update', 'market_securities/' . $sid, ['price' => $close, 'change' => round($close - $prev, 6),
                    'changePercent' => $prev ? round(($close - $prev) / $prev * 100, 2) : 0.0, 'updatedAt' => Value::serverTimestamp()]);
            }
        }
        if ($errors) {
            throw new FinanceException('import_refused', __('Nothing was imported.') . ' ' . implode(' | ', array_slice($errors, 0, 10)) . (count($errors) > 10 ? ' | …' : ''));
        }
        foreach (array_chunk($writes, 400) as $chunk) {
            $chunk[] = Audit::write($this->db, 'content.import_' . $what, $what, 'csv', null, ['writes' => count($chunk)]);
            $this->db->batch($chunk);
        }
        return ['rows' => count($rows), 'created' => $created];
    }
}
