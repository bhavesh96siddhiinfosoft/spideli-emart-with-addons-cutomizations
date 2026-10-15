<?php

namespace App\Finance;

/**
 * Amounts are integers in minor units (spec 1.3). XAF, XOF, GNF and RWF have
 * no decimals; every other finance currency has two.
 */
class Money
{
    private const ZERO_DECIMAL = ['XAF', 'XOF', 'GNF', 'RWF'];

    private const REFERENCE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function isCurrency($code)
    {
        return is_string($code) && preg_match('/^[A-Z]{3}$/', $code) === 1;
    }

    public static function exponent($currency)
    {
        return in_array($currency, self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    /**
     * "12.50" USD -> 1250; "1500" XAF -> 1500. Null for a blank input.
     * Throws when the value is not a non-negative amount with at most the
     * currency's decimals.
     */
    public static function toMinor($input, $currency)
    {
        $input = trim(str_replace([' ', "\u{00A0}", ','], ['', '', ''], (string)$input));
        if ($input === '') {
            return null;
        }
        $exp = self::exponent($currency);
        $pattern = $exp === 0 ? '/^\d+$/' : '/^\d+(\.\d{1,' . $exp . '})?$/';
        if (!preg_match($pattern, $input)) {
            throw new FinanceException('invalid_amount', $exp === 0
                ? "Amounts in $currency are whole numbers."
                : "Amounts in $currency have at most $exp decimals.");
        }
        [$whole, $frac] = array_pad(explode('.', $input), 2, '');
        $minor = (int)$whole * (10 ** $exp) + ($exp ? (int)str_pad($frac, $exp, '0') : 0);
        if ($minor > 9007199254740991) {
            throw new FinanceException('invalid_amount', 'The amount is too large.');
        }
        return $minor;
    }

    /** 1250 USD -> "12.50" (form value, no separators). */
    public static function toMajor($minor, $currency)
    {
        if ($minor === null || $minor === '') {
            return '';
        }
        $exp = self::exponent($currency);
        return $exp === 0 ? (string)(int)$minor : number_format($minor / (10 ** $exp), $exp, '.', '');
    }

    /** 245000 XAF -> "245 000 XAF"; 1250 USD -> "12.50 USD". */
    public static function format($minor, $currency)
    {
        $exp = self::exponent($currency);
        $neg = $minor < 0 ? '-' : '';
        return $neg . number_format(abs($minor) / (10 ** $exp), $exp, '.', ' ') . ' ' . $currency;
    }

    public static function random($length, $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $out = '';
        $max = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }

    /** tx_{epochMillis}_{8 chars} */
    public static function newTxId()
    {
        return 'tx_' . (int)floor(microtime(true) * 1000) . '_' . self::random(8);
    }

    /** SPF-yyMMdd-XXXXXX */
    public static function newReference()
    {
        return 'SPF-' . gmdate('ymd') . '-' . self::random(6, self::REFERENCE_ALPHABET);
    }
}
