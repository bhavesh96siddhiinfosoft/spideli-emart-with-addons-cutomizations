<?php

namespace App\Finance;

/**
 * The platform_settings/* documents of spec section 2, described once so the
 * forms, the validation and the "Offered / Not available" check all follow
 * the same definition.
 *
 * Region based: each document is finance_settings/{regionId}_{module}. Its
 * top-level "currencies" is not a form field - it is the region's currency
 * (Region Management), plus the convertible currencies for the wallet.
 *
 * Field types (lists are select2 dropdowns on the form):
 *  currencies   ISO-4217 codes from the Currencies list  ["XAF","EUR"]
 *  countries    ISO-3166 alpha-2 codes          ["CM","GA"]
 *  options      a subset of fixed values        ["wallet","wave"]
 *               ('options' => 'payment_methods' | 'deposit_methods' | 'payout_methods':
 *                only what the region's gateways offer, see Regions::methodsFor)
 *  list         free strings (a dropdown that also accepts new entries)
 *  int_list     whole numbers                   [3,6,12]
 *  bool, int, number, text, textarea, select
 *  money        per-currency minor units        {"XAF": 1000}; the form takes major units
 *  version      an int the panel increments when the text in 'of' changes
 *  kyc_documents, kyc_levels, banks, required_documents: structured, see parse()
 *
 * 'required' => true refuses to save without a value; 'need' lists what
 * the module needs to be offered, which is shown, not enforced.
 */
class SettingsSchema
{
    public const PAYMENT_METHODS = ['wallet', 'mobile_money', 'wave', 'card', 'paypal'];

    /** Method fields: the values each may hold before the region narrows them. */
    public const METHOD_SETS = [
        'payment_methods' => self::PAYMENT_METHODS,
        'deposit_methods' => ['mobile_money', 'wave', 'card', 'paypal'],
        'payout_methods' => ['mobile_money', 'wave', 'bank'],
    ];
    public const FREQUENCIES = ['weekly', 'biweekly', 'monthly'];

    public static function docs()
    {
        return ['wallet', 'kyc', 'transfers', 'bills', 'cards', 'savings', 'tontines', 'lending',
            'investments', 'insurance', 'financing', 'intelligence'];
    }

    public static function titles()
    {
        return [
            'wallet' => 'Wallet', 'kyc' => 'Identity verification (KYC)', 'transfers' => 'Transfers',
            'bills' => 'Bill payments', 'cards' => 'Virtual cards', 'savings' => 'Collective savings',
            'tontines' => 'Tontines', 'lending' => 'Member loans', 'investments' => 'Investments',
            'insurance' => 'Insurance', 'financing' => 'Project financing', 'intelligence' => 'Markets and intelligence',
        ];
    }

    /** [path => definition], with ['section' => title] entries as headings. */
    public static function fields($doc)
    {
        $pm = ['type' => 'options', 'options' => 'payment_methods', 'required' => true];

        switch ($doc) {
            case 'kyc':
                return [
                    'documentTypes' => ['type' => 'kyc_documents', 'required' => true, 'label' => 'Document types and sides',
                        'help' => 'Each document a customer can upload, and which of its sides. Pick a known type or type a new one.'],
                    'levels' => ['type' => 'kyc_levels', 'required' => true, 'label' => 'Verification levels',
                        'help' => 'Each verification level: its name, who it is for, and the documents it asks for.'],
                    'allowedContentTypes' => ['type' => 'options', 'required' => true, 'label' => 'Allowed file types',
                        'options' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
                    'maxFileBytes' => ['type' => 'int', 'required' => true, 'min' => 1, 'label' => 'Largest file (bytes)', 'help' => '10485760 = 10 MB'],
                ];

            case 'wallet':
                return [
                    ['section' => 'Add money'],
                    'deposit.enabled' => ['type' => 'bool', 'label' => 'Add money offered'],
                    'deposit.methods' => ['type' => 'options', 'options' => 'deposit_methods', 'label' => 'Methods'],
                    'deposit.min' => ['type' => 'money', 'label' => 'Smallest deposit'],
                    'deposit.max' => ['type' => 'money', 'label' => 'Largest deposit'],
                    ['section' => 'Withdraw'],
                    'withdraw.enabled' => ['type' => 'bool', 'label' => 'Withdraw offered'],
                    'withdraw.methods' => ['type' => 'options', 'options' => 'payout_methods', 'label' => 'Methods'],
                    'withdraw.min' => ['type' => 'money', 'label' => 'Smallest withdrawal'],
                    'withdraw.max' => ['type' => 'money', 'label' => 'Largest withdrawal'],
                    'withdraw.feePercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Fee (%)'],
                    'withdraw.feeFixed' => ['type' => 'money', 'label' => 'Fixed fee'],
                    'withdraw.feeMax' => ['type' => 'money', 'label' => 'Fee cap'],
                    'withdraw.delay' => ['type' => 'text', 'label' => 'Delay shown to users', 'help' => 'e.g. Within 24 hours'],
                    ['section' => 'Currency conversion'],
                    'convert.enabled' => ['type' => 'bool', 'label' => 'Conversion offered', 'help' => 'Needs at least 2 currencies.'],
                    'convert.currencies' => ['type' => 'currencies', 'label' => 'Convertible currencies',
                        'help' => 'The currencies a customer can convert between, the region\'s own included. They become wallet currencies too.'],
                    'convert.feePercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Fee (%)'],
                    'convert.maxRateAgeHours' => ['type' => 'int', 'min' => 1, 'default' => 48, 'label' => 'Oldest exchange rate accepted (hours)'],
                    ['section' => 'Security'],
                    'security.reauthAbove' => ['type' => 'money', 'label' => 'Ask for the password again from',
                        'help' => 'A currency left blank = always asked.'],
                ];

            case 'transfers':
                $fields = [
                    'sourceMethods' => ['type' => 'options', 'options' => 'payment_methods', 'required' => true, 'label' => 'What may pay a transfer'],
                    'security.reauthAbove' => ['type' => 'money', 'label' => 'Ask for the password again from', 'help' => 'A currency left blank = always asked.'],
                ];
                foreach (['internal' => 'Spideli to Spideli', 'mobile_money' => 'Mobile money', 'wave' => 'Wave', 'bank' => 'Bank'] as $ch => $title) {
                    $fields[] = ['section' => 'Channel: ' . $title];
                    $fields["channels.$ch.enabled"] = ['type' => 'bool', 'label' => 'Offered'];
                    if ($ch === 'mobile_money') {
                        $fields["channels.$ch.operators"] = ['type' => 'list', 'label' => 'Operators', 'help' => 'e.g. MTN Mobile Money, Orange Money. The channel is hidden without operators.'];
                    }
                    if ($ch === 'bank') {
                        $fields["channels.$ch.banks"] = ['type' => 'banks', 'label' => 'Banks', 'help' => 'The channel is hidden without banks.'];
                    }
                    $fields["channels.$ch.feePercent"] = ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Fee (%)'];
                    $fields["channels.$ch.feeFixed"] = ['type' => 'money', 'label' => 'Fixed fee'];
                    $fields["channels.$ch.feeMax"] = ['type' => 'money', 'label' => 'Fee cap'];
                    $fields["channels.$ch.min"] = ['type' => 'money', 'label' => 'Smallest transfer'];
                    $fields["channels.$ch.max"] = ['type' => 'money', 'label' => 'Largest transfer'];
                    $fields["channels.$ch.delay"] = ['type' => 'text', 'label' => 'Delay shown to users'];
                }
                return $fields;

            case 'bills':
                return [
                    'categories' => ['type' => 'options', 'required' => true, 'label' => 'Categories',
                        'options' => ['electricity', 'water', 'tv', 'internet', 'phone', 'gas', 'education', 'taxes', 'products', 'other']],
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'security.reauthAbove' => ['type' => 'money', 'label' => 'Ask for the password again from (compared with the total)',
                        'help' => 'A currency left blank = always asked.'],
                ];

            case 'cards':
                return [
                    'fundingMethods' => $pm + ['label' => 'Top-up methods'],
                    'issuerName' => ['type' => 'text', 'label' => 'Issuer name'],
                    'network' => ['type' => 'select', 'options' => ['visa', 'mastercard'], 'default' => 'visa', 'label' => 'Network'],
                    'maxCardsPerUser' => ['type' => 'int', 'min' => 1, 'max' => 10, 'label' => 'Cards per user'],
                    ['section' => 'Fees and top-ups'],
                    'issuanceFee' => ['type' => 'money', 'label' => 'Issuance fee'],
                    'monthlyFee' => ['type' => 'money', 'label' => 'Monthly fee (shown only, charged by the admin)'],
                    'renewalFee' => ['type' => 'money', 'label' => 'Renewal fee'],
                    'minFunding' => ['type' => 'money', 'label' => 'Smallest top-up'],
                    'maxFunding' => ['type' => 'money', 'label' => 'Largest top-up'],
                    'fundingFeePercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Top-up fee (%)'],
                    ['section' => 'Limits (ceilings of the user\'s own limits)'],
                    'limits.maxPerTransaction' => ['type' => 'money', 'label' => 'Per transaction'],
                    'limits.maxDaily' => ['type' => 'money', 'label' => 'Per day'],
                    'limits.maxMonthly' => ['type' => 'money', 'label' => 'Per month'],
                    ['section' => 'What the issuer supports'],
                    'capabilities.withdraw' => ['type' => 'bool', 'label' => 'Card to wallet'],
                    'capabilities.renew' => ['type' => 'bool', 'label' => 'Renewal'],
                    'capabilities.cardTransfer' => ['type' => 'bool', 'label' => 'Card to card'],
                    'capabilities.onlineToggle' => ['type' => 'bool', 'label' => 'Online payments switch'],
                    'capabilities.internationalToggle' => ['type' => 'bool', 'label' => 'International payments switch'],
                    'capabilities.secureDetails' => ['type' => 'select', 'options' => ['none', 'hosted', 'direct'], 'default' => 'none',
                        'label' => 'Showing the card number and CVV', 'help' => '"none" until an issuer page exists.'],
                    ['section' => 'Cardholder agreement'],
                    'terms' => ['type' => 'textarea', 'required' => true, 'label' => 'Terms'],
                    'termsVersion' => ['type' => 'version', 'of' => 'terms', 'label' => 'Terms version'],
                ];

            case 'savings':
                return [
                    'purposes' => ['type' => 'options', 'required' => true, 'label' => 'Purposes',
                        'options' => ['real_estate', 'education', 'travel', 'healthcare', 'business', 'other']],
                    'frequencies' => ['type' => 'options', 'options' => self::FREQUENCIES, 'label' => 'Frequencies'],
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'groupGoals' => ['type' => 'bool', 'label' => 'Group goals'],
                    'maxMembers' => ['type' => 'int', 'min' => 2, 'label' => 'Members per group goal'],
                    'earlyWithdrawal.allowed' => ['type' => 'bool', 'label' => 'Early withdrawal allowed'],
                ];

            case 'tontines':
                return [
                    'frequencies' => ['type' => 'options', 'options' => self::FREQUENCIES, 'required' => true, 'label' => 'Frequencies'],
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'minMembers' => ['type' => 'int', 'min' => 2, 'label' => 'Fewest members'],
                    'maxMembers' => ['type' => 'int', 'min' => 2, 'label' => 'Most members'],
                    'payoutOrderMethods' => ['type' => 'options', 'options' => ['fixed', 'random'], 'required' => true, 'label' => 'Payout order',
                        'help' => 'Without it every tontine creation is refused.'],
                    'minContribution' => ['type' => 'money', 'label' => 'Smallest contribution'],
                ];

            case 'lending':
                return [
                    'durationsMonths' => ['type' => 'int_list', 'required' => true, 'label' => 'Durations (months)', 'help' => 'e.g. 3, 6, 12'],
                    'repaymentFrequencies' => ['type' => 'options', 'options' => self::FREQUENCIES, 'required' => true, 'label' => 'Repayment frequencies'],
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'minAmount' => ['type' => 'money', 'label' => 'Smallest loan'],
                    'maxAmount' => ['type' => 'money', 'label' => 'Largest loan'],
                    'purposes' => ['type' => 'list', 'default' => ['other'], 'label' => 'Purposes'],
                    ['section' => 'Interest and fees'],
                    'interest.method' => ['type' => 'select', 'options' => ['flat', 'declining'], 'required' => true, 'label' => 'Interest method',
                        'help' => 'flat: on the principal; declining: annuity on the remaining balance.'],
                    'interest.annualPercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Annual interest (%)'],
                    'feePercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Fee taken from the disbursement (%)'],
                    'latePenaltyPercent' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Late penalty (%)'],
                    ['section' => 'Member vote'],
                    'voting.quorumPercent' => ['type' => 'int', 'min' => 1, 'max' => 100, 'required' => true, 'label' => 'Quorum (%)',
                        'help' => 'Without it every vote fails.'],
                    'voting.approvalPercent' => ['type' => 'int', 'min' => 1, 'max' => 100, 'required' => true, 'label' => 'Approval (%)'],
                    'voting.durationHours' => ['type' => 'int', 'min' => 1, 'required' => true, 'label' => 'Vote duration (hours)'],
                    'agreementTerms' => ['type' => 'textarea', 'label' => 'Loan agreement text'],
                ];

            case 'investments':
                return [
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'assetClasses' => ['type' => 'options', 'label' => 'Asset classes (filter order)',
                        'options' => ['money_market', 'bonds', 'equities', 'real_estate', 'agriculture', 'infrastructure', 'mixed']],
                    'riskDisclosure' => ['type' => 'textarea', 'required' => true, 'label' => 'Risk disclosure',
                        'help' => 'Shown and accepted before every subscription.'],
                    'simulator.maxYears' => ['type' => 'int', 'min' => 1, 'max' => 50, 'default' => 30, 'label' => 'Simulator: most years'],
                ];

            case 'insurance':
                return [
                    'categories' => ['type' => 'options', 'required' => true, 'label' => 'Categories',
                        'options' => ['health', 'life', 'auto', 'home', 'travel', 'business', 'agriculture', 'device', 'funeral']],
                    'paymentMethods' => $pm + ['label' => 'Payment methods'],
                    'claims.maxFiles' => ['type' => 'int', 'min' => 1, 'max' => 20, 'default' => 5, 'label' => 'Files per claim'],
                ];

            case 'financing':
                return [
                    'financingTypes' => ['type' => 'options', 'required' => true, 'label' => 'Financing types',
                        'options' => ['loan', 'equity', 'grant', 'guarantee', 'mixed']],
                    'sectors' => ['type' => 'list', 'required' => true, 'label' => 'Sectors'],
                    'stages' => ['type' => 'list', 'label' => 'Stages'],
                    'documentTypes' => ['type' => 'list', 'required' => true, 'label' => 'Document types'],
                    'requiredDocuments' => ['type' => 'required_documents', 'label' => 'Required documents',
                        'help' => 'Leave "Only for" empty to require the document for every financing type.'],
                    'minAmount' => ['type' => 'money', 'label' => 'Smallest amount'],
                    'maxAmount' => ['type' => 'money', 'label' => 'Largest amount'],
                    'durationsMonths' => ['type' => 'int_list', 'label' => 'Durations (months, for loan and mixed)'],
                    'allowedContentTypes' => ['type' => 'options', 'required' => true, 'label' => 'Allowed file types',
                        'options' => ['application/pdf', 'image/jpeg', 'image/png']],
                    'maxFileBytes' => ['type' => 'int', 'min' => 1, 'max' => 52428800, 'required' => true, 'label' => 'Largest file (bytes)',
                        'help' => 'At most 52428800 (50 MB).'],
                ];

            case 'intelligence':
                return [
                    ['section' => 'Economic indicators'],
                    'indicators.enabled' => ['type' => 'bool', 'label' => 'Offered'],
                    'indicators.countries' => ['type' => 'countries', 'label' => 'Countries (display order)'],
                    ['section' => 'Exchange rates'],
                    'exchangeRates.enabled' => ['type' => 'bool', 'label' => 'Offered'],
                    'exchangeRates.base' => ['type' => 'currency', 'label' => 'Base currency'],
                    'exchangeRates.quotes' => ['type' => 'currencies', 'label' => 'Quoted currencies'],
                    ['section' => 'Opportunity matching'],
                    'matching.enabled' => ['type' => 'bool', 'label' => 'Offered'],
                    'matching.currencies' => ['type' => 'currencies', 'label' => 'Currencies'],
                    'matching.sectors' => ['type' => 'list', 'label' => 'Sectors'],
                    'matching.types' => ['type' => 'options', 'options' => ['equity', 'loan', 'revenue_share', 'fund'], 'label' => 'Types'],
                    'matching.countries' => ['type' => 'countries', 'label' => 'Countries'],
                    'matching.weights.sector' => ['type' => 'number', 'min' => 0, 'label' => 'Weight: sector'],
                    'matching.weights.country' => ['type' => 'number', 'min' => 0, 'label' => 'Weight: country'],
                    'matching.weights.type' => ['type' => 'number', 'min' => 0, 'label' => 'Weight: type'],
                    'matching.weights.ticket' => ['type' => 'number', 'min' => 0, 'label' => 'Weight: ticket'],
                    'matching.weights.horizon' => ['type' => 'number', 'min' => 0, 'label' => 'Weight: horizon'],
                    'matching.minScore' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Lowest score shown'],
                    'matching.eligibility' => ['type' => 'textarea', 'label' => 'Eligibility'],
                    ['section' => 'Markets'],
                    'markets.enabled' => ['type' => 'bool', 'label' => 'Offered'],
                    'markets.stocks' => ['type' => 'bool', 'label' => 'Stocks'],
                    'markets.bonds' => ['type' => 'bool', 'label' => 'Bonds'],
                    'markets.ipos' => ['type' => 'bool', 'label' => 'IPOs'],
                    'markets.collections' => ['type' => 'bool', 'label' => 'Collections'],
                    'markets.news' => ['type' => 'bool', 'label' => 'News'],
                    ['section' => 'AI coach'],
                    'coach.enabled' => ['type' => 'bool', 'label' => 'Offered', 'help' => 'Keep off until an AI server exists.'],
                    'coach.consentText' => ['type' => 'textarea', 'label' => 'Consent text'],
                    'coach.consentVersion' => ['type' => 'version', 'of' => 'coach.consentText', 'label' => 'Consent version'],
                    'coach.topics' => ['type' => 'list', 'label' => 'Topics'],
                    'coach.maxMessageLength' => ['type' => 'int', 'min' => 1, 'label' => 'Longest message (characters)'],
                ];
        }
        throw new FinanceException('unknown_settings', 'Unknown settings document.');
    }

    /** Only the real fields, without the section headings. */
    public static function realFields($doc)
    {
        return array_filter(self::fields($doc), function ($f, $k) {
            return is_string($k);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /* ------------------------------------------------------- nested arrays */

    public static function getPath(array $data, $path)
    {
        $ref = $data;
        foreach (explode('.', $path) as $s) {
            if (!is_array($ref) || !array_key_exists($s, $ref)) {
                return null;
            }
            $ref = $ref[$s];
        }
        return $ref;
    }

    public static function setPath(array &$data, $path, $value)
    {
        $segments = explode('.', $path);
        $ref = &$data;
        foreach (array_slice($segments, 0, -1) as $s) {
            if (!isset($ref[$s]) || !is_array($ref[$s])) {
                $ref[$s] = [];
            }
            $ref = &$ref[$s];
        }
        $ref[end($segments)] = $value;
    }

    public static function unsetPath(array &$data, $path)
    {
        $segments = explode('.', $path);
        $ref = &$data;
        foreach (array_slice($segments, 0, -1) as $s) {
            if (!isset($ref[$s]) || !is_array($ref[$s])) {
                return;
            }
            $ref = &$ref[$s];
        }
        unset($ref[end($segments)]);
    }

    /* ------------------------------------------------- form values to data */

    /**
     * Builds the new document from the submitted form, on top of the stored
     * one so fields this form does not manage are kept.
     * Returns [data, errors (path => message)].
     */
    public static function parse($doc, array $input, array $stored, $regionCurrency, array $regionMethods = null)
    {
        $data = $stored;
        $errors = [];
        $fields = self::realFields($doc);

        // The document's currencies come from the region, never from the form.
        // The wallet also holds its convertible currencies (a conversion moves
        // money between two wallets of the user).
        if (!Money::isCurrency($regionCurrency)) {
            return [$data, ['currencies' => 'This region has no currency. Set it in Region Management.']];
        }
        $currencies = [$regionCurrency];
        if ($doc === 'wallet') {
            try {
                $currencies = array_values(array_unique(array_merge($currencies,
                    self::parseCodes($input[self::inputName('convert.currencies')] ?? [], 3))));
            } catch (FinanceException $e) {
                // reported by the field itself below
            }
        }
        if ($doc !== 'intelligence') {
            $data['currencies'] = $currencies;
        }

        foreach ($fields as $path => $f) {
            $raw = $input[self::inputName($path)] ?? null;
            try {
                $value = self::parseValue($f, $raw, $currencies, $input, $path);
            } catch (FinanceException $e) {
                $errors[$path] = $e->getMessage();
                continue;
            }
            if ($f['type'] === 'version') {
                continue;   // set after every text is known
            }
            // A method no gateway of this region provides is not offered there.
            if ($regionMethods !== null && self::isMethodField($f) && is_array($value)) {
                $value = array_values(array_intersect($value, $regionMethods));
            }
            $empty = $value === null || $value === [] || $value === '';
            if ($empty && !empty($f['required'])) {
                $errors[$path] = 'Required.';
                continue;
            }
            if ($empty && array_key_exists('default', $f) && $f['type'] !== 'bool') {
                $value = $f['default'];
                $empty = false;
            }
            if ($empty && $f['type'] !== 'money') {
                self::unsetPath($data, $path);
            } elseif ($f['type'] === 'money' && $value === []) {
                self::unsetPath($data, $path);
            } else {
                self::setPath($data, $path, $value);
            }
        }

        // Versions: start at 1, add 1 whenever the text changes.
        foreach ($fields as $path => $f) {
            if ($f['type'] !== 'version') {
                continue;
            }
            $oldText = self::getPath($stored, $f['of']);
            $newText = self::getPath($data, $f['of']);
            $old = self::getPath($stored, $path);
            $old = is_int($old) && $old >= 1 ? $old : 0;
            if ($newText === null || $newText === '') {
                if ($old) {
                    self::setPath($data, $path, $old);
                }
                continue;
            }
            self::setPath($data, $path, ($old === 0 || $oldText !== $newText) ? $old + 1 : $old);
        }

        $errors += self::crossChecks($doc, $data);
        return [$data, $errors];
    }

    public static function inputName($path)
    {
        return str_replace('.', '__', $path);
    }

    private static function parseValue(array $f, $raw, array $currencies, array $input, $path)
    {
        switch ($f['type']) {
            case 'bool':
                return !empty($raw) && $raw !== '0';
            case 'currencies':
                return self::parseCodes($raw, 3);
            case 'countries':
                return self::parseCodes($raw, 2);
            case 'currency':
                $codes = self::parseCodes($raw, 3);
                if (count($codes) > 1) {
                    throw new FinanceException('invalid', 'One currency only.');
                }
                return $codes[0] ?? null;
            case 'options':
                $raw = is_array($raw) ? $raw : [];
                // Keep the order of the options, which is the display order.
                return array_values(array_filter(self::optionValues($f), function ($o) use ($raw) {
                    return in_array($o, $raw, true);
                }));
            case 'list':
                return self::parseList($raw);
            case 'int_list':
                $out = [];
                foreach (self::parseList($raw) as $v) {
                    if (!preg_match('/^\d+$/', $v) || (int)$v < 1) {
                        throw new FinanceException('invalid', 'Whole numbers above 0 only.');
                    }
                    $out[] = (int)$v;
                }
                $out = array_values(array_unique($out));
                sort($out);
                return $out;
            case 'int':
                $raw = trim((string)$raw);
                if ($raw === '') {
                    return null;
                }
                if (!preg_match('/^-?\d+$/', $raw)) {
                    throw new FinanceException('invalid', 'A whole number.');
                }
                return self::range((int)$raw, $f);
            case 'number':
                $raw = trim((string)$raw);
                if ($raw === '') {
                    return null;
                }
                if (!is_numeric($raw)) {
                    throw new FinanceException('invalid', 'A number.');
                }
                $n = $raw + 0;
                return self::range(is_float($n) && floor($n) == $n && abs($n) < PHP_INT_MAX ? (int)$n : $n, $f);
            case 'text':
            case 'textarea':
                $raw = trim(str_replace("\r\n", "\n", (string)$raw));
                return $raw === '' ? null : $raw;
            case 'select':
                $raw = (string)$raw;
                if ($raw === '') {
                    return null;
                }
                if (!in_array($raw, $f['options'], true)) {
                    throw new FinanceException('invalid', 'Not an allowed value.');
                }
                return $raw;
            case 'money':
                $raw = is_array($raw) ? $raw : [];
                $out = [];
                foreach ($raw as $code => $amount) {
                    if (!Money::isCurrency($code) || !in_array($code, $currencies, true)) {
                        continue;   // an amount belongs only to a currency of this region's document
                    }
                    $minor = Money::toMinor($amount, $code);
                    if ($minor !== null) {
                        $out[$code] = $minor;
                    }
                }
                ksort($out);
                return $out;
            case 'version':
                return null;
            case 'kyc_documents':
                return self::parseKycDocuments(self::rows($raw));
            case 'kyc_levels':
                return self::parseKycLevels(self::rows($raw));
            case 'banks':
                $out = [];
                foreach (self::rows($raw) as $row) {
                    $code = trim((string)($row['code'] ?? ''));
                    $bank = trim((string)($row['name'] ?? ''));
                    if ($code === '' && $bank === '') {
                        continue;
                    }
                    if ($code === '' || $bank === '') {
                        throw new FinanceException('invalid', 'Each bank needs a code and a name.');
                    }
                    $out[] = ['code' => $code, 'name' => $bank];
                }
                return $out;
            case 'required_documents':
                $out = [];
                foreach (self::rows($raw) as $row) {
                    $type = trim((string)($row['type'] ?? ''));
                    if ($type === '') {
                        continue;
                    }
                    $types = self::parseList($row['financingTypes'] ?? []);
                    if (array_diff($types, ['loan', 'equity', 'grant', 'guarantee', 'mixed'])) {
                        throw new FinanceException('invalid', 'Not an allowed value.');
                    }
                    // No financing type chosen = required for every type.
                    $out[] = $types ? ['type' => $type, 'financingTypes' => array_values($types)] : $type;
                }
                return $out;
        }
        throw new \LogicException('Unknown field type ' . $f['type']);
    }

    private static function range($n, array $f)
    {
        if (isset($f['min']) && $n < $f['min']) {
            throw new FinanceException('invalid', 'At least ' . $f['min'] . '.');
        }
        if (isset($f['max']) && $n > $f['max']) {
            throw new FinanceException('invalid', 'At most ' . $f['max'] . '.');
        }
        return $n;
    }

    public static function parseList($raw)
    {
        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = preg_split('/[\r\n,]+/', (string)$raw);
        }
        $items = array_values(array_filter(array_map('trim', $items), 'strlen'));
        return array_values(array_unique($items));
    }

    /** The allowed values of an 'options' field. */
    public static function optionValues(array $f)
    {
        return is_string($f['options']) ? self::METHOD_SETS[$f['options']] : $f['options'];
    }

    /** A payment-method field, narrowed to what each region's gateways offer. */
    public static function isMethodField(array $f)
    {
        return $f['type'] === 'options' && is_string($f['options']);
    }

    private static function parseCodes($raw, $length)
    {
        $codes = array_map('strtoupper', self::parseList(is_array($raw) ? $raw : str_replace(' ', ',', (string)$raw)));
        foreach ($codes as $c) {
            if (!preg_match('/^[A-Z]{' . $length . '}$/', $c)) {
                throw new FinanceException('invalid', $length === 3
                    ? "\"$c\" is not a 3-letter currency code."
                    : "\"$c\" is not a 2-letter country code.");
            }
        }
        if ($length === 3) {
            foreach ($codes as $c) {
                if ($c === 'JPY') {
                    throw new FinanceException('invalid', 'JPY is not offered: finance treats only XAF, XOF, GNF and RWF as zero-decimal.');
                }
            }
        }
        return $codes;
    }

    /** The rows of a repeatable field, in form order, empty rows kept out by the callers. */
    private static function rows($raw)
    {
        return is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
    }

    /** Rows {type, front, back} -> { type: { sides: [...] } } */
    private static function parseKycDocuments(array $rows)
    {
        $out = [];
        foreach ($rows as $row) {
            $type = trim((string)($row['type'] ?? ''));
            if ($type === '') {
                continue;
            }
            if (!preg_match('/^[a-z0-9_]+$/', $type)) {
                throw new FinanceException('invalid', "\"$type\": use lower-case letters, digits and _ only.");
            }
            $sides = [];
            if (!empty($row['front']) && $row['front'] !== '0') {
                $sides[] = 'front';
            }
            if (!empty($row['back']) && $row['back'] !== '0') {
                $sides[] = 'back';
            }
            if (!$sides) {
                throw new FinanceException('invalid', "Tick at least one side for \"$type\".");
            }
            $out[$type] = ['sides' => $sides];
        }
        return $out;
    }

    /** Rows {name, accountTypes[], groups: [[types...], ...]} -> { name: { accountTypes, requiredDocuments: [{anyOf}] } } */
    private static function parseKycLevels(array $rows)
    {
        $out = [];
        foreach ($rows as $row) {
            $level = trim((string)($row['name'] ?? ''));
            if ($level === '') {
                continue;
            }
            if (!preg_match('/^[a-z0-9_]+$/', $level)) {
                throw new FinanceException('invalid', "\"$level\": use lower-case letters, digits and _ only.");
            }
            $accountTypes = self::parseList($row['accountTypes'] ?? []);
            if (!$accountTypes || array_diff($accountTypes, ['individual', 'business'])) {
                throw new FinanceException('invalid', "Choose the account types of the level \"$level\".");
            }
            $groups = [];
            foreach ((array)($row['groups'] ?? []) as $g) {
                $types = self::parseList(is_array($g) ? $g : []);
                if ($types) {
                    $groups[] = ['anyOf' => $types];
                }
            }
            if (!$groups) {
                throw new FinanceException('invalid', "The level \"$level\" needs at least one required document.");
            }
            $out[$level] = ['accountTypes' => array_values($accountTypes), 'requiredDocuments' => $groups];
        }
        return $out;
    }

    /**
     * A repeatable field's stored value as form rows (the shape the form posts).
     */
    public static function formRows(array $f, $value)
    {
        $value = is_array($value) ? $value : [];
        switch ($f['type']) {
            case 'kyc_documents':
                $rows = [];
                foreach ($value as $type => $d) {
                    $sides = (array)($d['sides'] ?? []);
                    $rows[] = ['type' => (string)$type, 'front' => in_array('front', $sides, true), 'back' => in_array('back', $sides, true)];
                }
                return $rows;
            case 'kyc_levels':
                $rows = [];
                foreach ($value as $level => $l) {
                    $rows[] = ['name' => (string)$level, 'accountTypes' => (array)($l['accountTypes'] ?? []),
                        'groups' => array_map(function ($g) { return (array)($g['anyOf'] ?? []); }, (array)($l['requiredDocuments'] ?? []))];
                }
                return $rows;
            case 'banks':
                return array_map(function ($b) { return ['code' => (string)($b['code'] ?? ''), 'name' => (string)($b['name'] ?? '')]; }, $value);
            case 'required_documents':
                return array_map(function ($r) {
                    return is_array($r) ? ['type' => (string)($r['type'] ?? ''), 'financingTypes' => (array)($r['financingTypes'] ?? [])]
                                        : ['type' => (string)$r, 'financingTypes' => []];
                }, $value);
        }
        return [];
    }

    /** A posted repeatable field back as rows (after a refused save), normalised. */
    public static function postedRows(array $f, $raw)
    {
        $rows = self::rows($raw);
        if ($f['type'] === 'kyc_documents') {
            foreach ($rows as &$r) {
                $r['front'] = !empty($r['front']) && $r['front'] !== '0';
                $r['back'] = !empty($r['back']) && $r['back'] !== '0';
            }
        }
        if ($f['type'] === 'kyc_levels') {
            foreach ($rows as &$r) {
                $r['groups'] = array_values(array_filter((array)($r['groups'] ?? []), 'is_array'));
            }
        }
        return $rows;
    }

    /** Rules between fields (spec 6.2). */
    private static function crossChecks($doc, array $d)
    {
        $e = [];
        $money = function ($minPath, $maxPath) use ($d, &$e) {
            $min = self::getPath($d, $minPath) ?: [];
            $max = self::getPath($d, $maxPath) ?: [];
            foreach ($min as $c => $v) {
                if (isset($max[$c]) && $max[$c] < $v) {
                    $e[$maxPath] = "The largest amount in $c is below the smallest.";
                }
            }
        };

        switch ($doc) {
            case 'kyc':
                $types = array_keys(self::getPath($d, 'documentTypes') ?: []);
                foreach (self::getPath($d, 'levels') ?: [] as $level => $l) {
                    foreach ($l['requiredDocuments'] ?? [] as $g) {
                        foreach ($g['anyOf'] ?? [] as $t) {
                            if (!in_array($t, $types, true)) {
                                $e['levels'] = "The level $level needs \"$t\", which is not a document type.";
                            }
                        }
                    }
                }
                if (!in_array('image/jpeg', self::getPath($d, 'allowedContentTypes') ?: [], true)) {
                    $e['allowedContentTypes'] = 'Must include image/jpeg: every KYC upload is a JPEG.';
                }
                break;
            case 'wallet':
                $money('deposit.min', 'deposit.max');
                $money('withdraw.min', 'withdraw.max');
                $conv = self::getPath($d, 'convert.currencies') ?: [];
                if (self::getPath($d, 'convert.enabled') && count($conv) < 2) {
                    $e['convert.currencies'] = 'Conversion needs at least 2 convertible currencies.';
                }
                break;
            case 'transfers':
                foreach (['internal', 'mobile_money', 'wave', 'bank'] as $ch) {
                    $money("channels.$ch.min", "channels.$ch.max");
                }
                break;
            case 'cards':
                $money('minFunding', 'maxFunding');
                break;
            case 'tontines':
                $min = self::getPath($d, 'minMembers');
                $max = self::getPath($d, 'maxMembers');
                if (is_int($min) && is_int($max) && $max < $min) {
                    $e['maxMembers'] = 'Must be at least the fewest members.';
                }
                break;
            case 'lending':
            case 'financing':
                $money('minAmount', 'maxAmount');
                if ($doc === 'financing') {
                    $types = self::getPath($d, 'documentTypes') ?: [];
                    foreach (self::getPath($d, 'requiredDocuments') ?: [] as $r) {
                        $t = is_array($r) ? $r['type'] : $r;
                        if (!in_array($t, $types, true)) {
                            $e['requiredDocuments'] = "\"$t\" is not one of the document types.";
                        }
                    }
                }
                break;
        }
        return $e;
    }

    /* ------------------------------------------------------- availability */

    /**
     * What the module still lacks to be offered to customers (spec 2: a
     * missing or invalid document makes the module "not available yet").
     * Empty = offered.
     */
    public static function missing($doc, $data)
    {
        if ($data === null) {
            return ['the settings document'];
        }
        $m = [];
        if ($doc !== 'intelligence' && !(self::getPath($data, 'currencies') ?: [])) {
            $m[] = 'the region currency';
        }
        foreach (self::realFields($doc) as $path => $f) {
            $v = self::getPath($data, $path);
            if (!empty($f['required']) && ($v === null || $v === [] || $v === '')) {
                $m[] = $f['label'];
            }
        }
        if ($doc === 'cards' && !(is_int(self::getPath($data, 'termsVersion')) && self::getPath($data, 'termsVersion') >= 1)) {
            $m[] = 'Terms version';
        }
        if ($doc === 'intelligence') {
            $on = function ($p) use ($data) { return self::getPath($data, $p) === true; };
            if (!$on('indicators.enabled') && !$on('exchangeRates.enabled') && !$on('matching.enabled') && !$on('markets.enabled') && !$on('coach.enabled')) {
                $m[] = 'every part is switched off';
            }
        }
        return array_values(array_unique($m));
    }

    /** Parts that are switched on but will stay hidden, worth a warning. */
    public static function warnings($doc, $data)
    {
        $w = [];
        if (!$data) {
            return $w;
        }
        $g = function ($p) use ($data) { return self::getPath($data, $p); };
        if ($doc === 'transfers') {
            if ($g('channels.mobile_money.enabled') && !$g('channels.mobile_money.operators')) {
                $w[] = 'Mobile money is on but has no operators, so it stays hidden.';
            }
            if ($g('channels.bank.enabled') && !$g('channels.bank.banks')) {
                $w[] = 'Bank is on but has no banks, so it stays hidden.';
            }
        }
        if ($doc === 'wallet' && $g('convert.enabled') && count($g('convert.currencies') ?: []) < 2) {
            $w[] = 'Conversion is on but needs at least 2 currencies.';
        }
        if ($doc === 'intelligence') {
            if ($g('indicators.enabled') && !$g('indicators.countries')) {
                $w[] = 'Indicators are on but have no countries.';
            }
            if ($g('exchangeRates.enabled') && (!$g('exchangeRates.base') || !$g('exchangeRates.quotes'))) {
                $w[] = 'Exchange rates are on but need a base and at least one quoted currency.';
            }
            if ($g('coach.enabled') && (!$g('coach.consentVersion') || !$g('coach.consentText'))) {
                $w[] = 'The coach is on but has no consent text.';
            }
        }
        return $w;
    }

    /** The stored value as the form shows it. */
    public static function formValue(array $f, $value)
    {
        switch ($f['type']) {
            case 'currencies':
            case 'countries':
            case 'int_list':
            case 'list':
            case 'options':
                // select2: the selected values
                return is_array($value) ? array_values(array_map('strval', array_filter($value, 'is_scalar'))) : [];
            case 'kyc_documents':
                $lines = [];
                foreach (is_array($value) ? $value : [] as $type => $d) {
                    $lines[] = $type . ': ' . implode(', ', $d['sides'] ?? []);
                }
                return implode("\n", $lines);
            case 'kyc_levels':
                $lines = [];
                foreach (is_array($value) ? $value : [] as $level => $l) {
                    $groups = array_map(function ($g) { return implode(', ', $g['anyOf'] ?? []); }, $l['requiredDocuments'] ?? []);
                    $lines[] = $level . ' | ' . implode(', ', $l['accountTypes'] ?? []) . ' | ' . implode('; ', $groups);
                }
                return implode("\n", $lines);
            case 'banks':
                return implode("\n", array_map(function ($b) { return ($b['code'] ?? '') . ' | ' . ($b['name'] ?? ''); }, is_array($value) ? $value : []));
            case 'required_documents':
                return implode("\n", array_map(function ($r) {
                    return is_array($r) ? $r['type'] . ': ' . implode(', ', $r['financingTypes'] ?? []) : $r;
                }, is_array($value) ? $value : []));
            case 'bool':
                return (bool)$value;
            default:
                return is_scalar($value) ? (string)$value : '';
        }
    }
}
