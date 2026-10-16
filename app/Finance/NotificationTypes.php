<?php

namespace App\Finance;

/**
 * How each Spideli Finance notification the panel sends goes out (spec 5.4,
 * 6.14). The texts are the App Notification (dynamic_notification)
 * templates; this only says, per type:
 *
 *   category  the financeNotificationPrefs category the customer chooses
 *             (payments, groups, loans, investments, insurance); null =
 *             always sent (security notices and answers to the customer's
 *             own request: identity, financing, support, card security).
 *   sms       also sent by SMS (critical: withdrawals, transfers, bill
 *             codes, card security, overdue).
 *   email     also sent by e-mail (receipts and alerts).
 *
 * A type not listed goes as a push only, with the category of its prefix.
 */
class NotificationTypes
{
    /** type => [category, sms, email] */
    private const TYPES = [
        'kyc_additional_info_required' => [null, false, true],
        'kyc_approved' => [null, false, true],
        'kyc_rejected' => [null, false, true],

        'wallet_withdrawal_completed' => ['payments', true, true],
        'wallet_withdrawal_failed' => ['payments', true, true],
        'transfer_sent' => ['payments', true, true],
        'transfer_reversed' => ['payments', true, true],
        'bill_paid' => ['payments', false, true],
        'bill_token' => ['payments', true, true],
        'bill_reversed' => ['payments', true, true],

        // Virtual cards (sent once cards are built with an issuer).
        'card_created' => ['payments', false, true],
        'card_creation_failed' => ['payments', false, true],
        'card_funded' => ['payments', false, true],
        'card_funding_failed' => ['payments', false, true],
        'card_transaction_declined' => ['payments', true, true],
        'card_refund' => ['payments', false, true],
        'card_withdrawal_completed' => ['payments', false, true],
        'card_expiring' => ['payments', false, true],
        'card_renewed' => ['payments', false, true],
        'card_frozen' => [null, true, true],
        'card_unfrozen' => [null, true, true],
        'card_details_viewed' => [null, true, true],
        'card_limits_updated' => [null, true, true],
        'card_limits_rejected' => [null, true, true],
        'card_terminated' => [null, true, true],

        'savings_contribution_due' => ['groups', false, true],
        'savings_goal_completed' => ['groups', false, true],
        'savings_withdrawal_completed' => ['groups', false, true],
        'savings_withdrawal_rejected' => ['groups', false, true],
        'tontine_contribution_due' => ['groups', false, true],
        'tontine_contribution_overdue' => ['groups', true, true],
        'tontine_payout_paid' => ['groups', false, true],

        'loan_submitted' => ['loans', false, true],
        'loan_vote_requested' => ['loans', false, false],
        'loan_approved' => ['loans', false, true],
        'loan_rejected' => ['loans', false, true],
        'loan_funding_open' => ['loans', false, false],
        'loan_repayment_due' => ['loans', false, true],
        'loan_repayment_overdue' => ['loans', true, true],
        'loan_lender_repayment' => ['loans', false, true],

        'investment_payment_confirmed' => ['investments', false, true],
        'investment_units_allocated' => ['investments', false, true],
        'investment_subscription_rejected' => ['investments', false, true],
        'investment_redemption_processed' => ['investments', false, true],
        'investment_redemption_settled' => ['investments', false, true],
        'investment_redemption_rejected' => ['investments', false, true],
        'investment_report_available' => ['investments', false, true],
        'investment_terms_updated' => ['investments', false, true],
        'intelligence_new_match' => ['investments', false, false],
        'intelligence_interest_received' => ['investments', false, true],

        'insurance_quote_ready' => ['insurance', false, true],
        'insurance_quote_rejected' => ['insurance', false, true],
        'insurance_policy_active' => ['insurance', false, true],
        'insurance_policy_rejected' => ['insurance', false, true],
        'insurance_premium_due' => ['insurance', false, true],
        'insurance_premium_overdue' => ['insurance', true, true],
        'insurance_policy_lapsed' => ['insurance', true, true],
        'insurance_renewal_due' => ['insurance', false, true],
        'insurance_policy_renewed' => ['insurance', false, true],
        'insurance_policy_cancelled' => ['insurance', false, true],
        'insurance_claim_received' => ['insurance', false, true],
        'insurance_claim_info_requested' => ['insurance', false, true],
        'insurance_claim_approved' => ['insurance', false, true],
        'insurance_claim_rejected' => ['insurance', false, true],
        'insurance_claim_paid' => ['insurance', false, true],

        'financing_under_review' => [null, false, true],
        'financing_info_required' => [null, false, true],
        'financing_document_rejected' => [null, false, true],
        'financing_approved' => [null, false, true],
        'financing_rejected' => [null, false, true],
        'financing_funded' => [null, false, true],
        'financing_closed' => [null, false, true],

        'support_ticket_resolved' => [null, false, true],
    ];

    /** ['category' => ..., 'sms' => bool, 'email' => bool], or null for a push-only type. */
    public static function get($type)
    {
        if (!isset(self::TYPES[$type])) {
            return null;
        }
        [$category, $sms, $email] = self::TYPES[$type];
        return compact('category', 'sms', 'email');
    }

    /** The preference category of a type (null = always sent); a type not listed counts by its prefix. */
    public static function category($type)
    {
        if (array_key_exists($type, self::TYPES)) {
            return self::TYPES[$type][0];
        }
        foreach (['loan_' => 'loans', 'savings_' => 'groups', 'tontine_' => 'groups', 'investment_' => 'investments',
                  'intelligence_' => 'investments', 'insurance_' => 'insurance', 'financing_' => null, 'kyc_' => null, 'support_' => null] as $prefix => $category) {
            if (strpos($type, $prefix) === 0) {
                return $category;
            }
        }
        return 'payments';
    }

    /** The {placeholders} of a text. */
    public static function placeholders($text)
    {
        preg_match_all('/\{([A-Za-z0-9_]+)\}/', (string)$text, $m);
        return array_values(array_unique($m[1]));
    }

    /**
     * Whether a customer allows a category on a channel. Defaults when the
     * customer never chose (spec 3.1): push everything; SMS and e-mail only
     * payments. A null category is always allowed.
     */
    public static function allowed(array $user, $channel, $category)
    {
        if ($category === null) {
            return true;
        }
        $choice = $user['financeNotificationPrefs'][$channel][$category] ?? null;
        if (is_bool($choice)) {
            return $choice;
        }
        return $channel === 'push' || $category === 'payments';
    }
}
