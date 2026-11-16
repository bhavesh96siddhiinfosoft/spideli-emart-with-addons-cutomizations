<?php

namespace App\Finance;

/**
 * Finance F18 ("About loan", client 16 Nov): what a loan costs and who gets it.
 *
 *  - Borrower: an origination fee (% of the principal, deducted at disbursement)
 *    and an insurance premium (% of the principal by the borrower's risk band),
 *    split between the insurer and Spideli (brokerage commission).
 *  - Lender: receives the interest minus Spideli's commission on it, and a share
 *    of the late fees (Spideli keeps the rest).
 *
 * The rates are copied onto the loan when it is created (loan_applications.fees),
 * so a change of the settings never alters a running loan; a loan without
 * `fees` (created before F18) costs nothing extra. Amounts are minor units.
 */
class LoanFees
{
    const BANDS = ['excellent', 'good', 'fair', 'poor'];

    /** Holding account for the insurer's share of loan premiums, one per currency. */
    public static function insurerAccountId($currency)
    {
        return 'loan_insurance_' . $currency;
    }

    private static function pct($v, $max = 100.0)
    {
        $v = is_numeric($v) ? (float)$v : 0.0;
        return (!is_finite($v) || $v < 0) ? 0.0 : min($v, $max);
    }

    /**
     * The rates to copy onto a new loan, from the region's lending settings and
     * the borrower's risk band (Excellent / Good / Fair / Poor; unknown = Poor).
     */
    public static function snapshot(array $lending, $band)
    {
        $band = strtolower(trim((string)$band));
        if (!in_array($band, self::BANDS, true)) {
            $band = 'poor';
        }
        $ins = (array)($lending['insurance'] ?? []);
        $late = (array)($lending['lateFee'] ?? []);
        return [
            'originationPercent' => self::pct($lending['feePercent'] ?? 0),
            'riskBand' => $band,
            'insurancePremiumPercent' => self::pct(((array)($ins['premiumPercent'] ?? []))[$band] ?? 0),
            'insurerSharePercent' => self::pct($ins['insurerSharePercent'] ?? 0),
            'lenderCommissionPercent' => self::pct($lending['lenderCommissionPercent'] ?? 0),
            'lateFeePlatformSharePercent' => self::pct($late['platformSharePercent'] ?? 0),
        ];
    }

    /**
     * At disbursement: [origination fee, premium, insurer's share, Spideli's
     * share of the premium, what the borrower's wallet receives].
     */
    public static function disbursement($principal, array $fees)
    {
        $principal = (int)$principal;
        $origination = (int)round($principal * self::pct($fees['originationPercent'] ?? 0) / 100);
        $premium = (int)round($principal * self::pct($fees['insurancePremiumPercent'] ?? 0) / 100);
        if ($origination + $premium >= $principal) {   // the borrower always receives something
            $premium = max(0, min($premium, $principal - 1));
            $origination = max(0, min($origination, $principal - 1 - $premium));
        }
        $insurer = (int)round($premium * self::pct($fees['insurerSharePercent'] ?? 0) / 100);
        return [
            'origination' => $origination,
            'premium' => $premium,
            'insurerShare' => $insurer,
            'brokerage' => $premium - $insurer,
            'toBorrower' => $principal - $origination - $premium,
        ];
    }

    /**
     * What the borrower's repayments have covered so far: repaid money fills the
     * instalments in order, each one's late fee first, then its interest, then
     * its principal. Returns ['principal' =>, 'interest' =>, 'penalty' =>].
     * Without a schedule everything counts as principal.
     */
    public static function covered(array $installments, $repaid)
    {
        $left = max(0, (int)$repaid);
        $out = ['principal' => 0, 'interest' => 0, 'penalty' => 0];
        usort($installments, function ($a, $b) { return (int)($a['number'] ?? 0) <=> (int)($b['number'] ?? 0); });
        foreach ($installments as $it) {
            foreach (['penalty', 'interest', 'principal'] as $part) {
                $take = min($left, max(0, (int)($it[$part] ?? 0)));
                $out[$part] += $take;
                $left -= $take;
            }
        }
        $out['principal'] += $left;   // paid beyond the schedule
        return $out;
    }

    /**
     * One lender's cumulative position: [gross share of the repayments,
     * Spideli's commission on their interest, Spideli's share of their late fees].
     * Net to the lender = gross - commission - late fee share.
     */
    public static function lenderShare($lent, $principal, $repaid, array $covered, array $fees)
    {
        $lent = (int)$lent;
        $principal = max(1, (int)$principal);
        $gross = intdiv((int)$repaid * $lent, $principal);
        $interest = intdiv($covered['interest'] * $lent, $principal);
        $penalty = intdiv($covered['penalty'] * $lent, $principal);
        $commission = intdiv((int)round($interest * self::pct($fees['lenderCommissionPercent'] ?? 0) * 100), 10000);
        $lateShare = intdiv((int)round($penalty * self::pct($fees['lateFeePlatformSharePercent'] ?? 0) * 100), 10000);
        return [$gross, $commission, $lateShare];
    }

    /**
     * What to post now for one funding: ['net' =>, 'commission' =>, 'lateFee' =>],
     * from its cumulative position minus what was already paid and withheld;
     * never more in total than the lender's gross share.
     */
    public static function dueNow(array $funding, $principal, $repaid, array $covered, array $fees)
    {
        [$gross, $commission, $late] = self::lenderShare($funding['amount'] ?? 0, $principal, $repaid, $covered, $fees);
        $collected = (int)($funding['collected'] ?? 0);
        $withheldC = (int)($funding['commissionWithheld'] ?? 0);
        $withheldL = (int)($funding['lateFeeWithheld'] ?? 0);
        $available = max(0, $gross - $collected - $withheldC - $withheldL);
        $net = min(max(0, $gross - $commission - $late - $collected), $available);
        $c = min(max(0, $commission - $withheldC), $available - $net);
        $lf = min(max(0, $late - $withheldL), $available - $net - $c);
        return ['net' => $net, 'commission' => $c, 'lateFee' => $lf];
    }

    /** True when every part of a funding's gross share is paid or withheld. */
    public static function settled(array $funding, $principal, $repaid)
    {
        $gross = intdiv((int)$repaid * (int)($funding['amount'] ?? 0), max(1, (int)$principal));
        return (int)($funding['collected'] ?? 0) + (int)($funding['commissionWithheld'] ?? 0) + (int)($funding['lateFeeWithheld'] ?? 0) >= $gross;
    }
}
