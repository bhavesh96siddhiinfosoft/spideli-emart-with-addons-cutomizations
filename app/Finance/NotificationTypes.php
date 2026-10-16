<?php

namespace App\Finance;

/**
 * Every Spideli Finance notification type (spec 6.14), with who sends it,
 * the customer's preference category, the extra channels and the default
 * English text used when the template is missing.
 *
 *   by        panel = sent by this panel; app = sent by the customer apps /
 *             website (the panel never sends it); cards = virtual cards,
 *             not built until an issuer is contracted.
 *   category  financeNotificationPrefs category (payments, groups, loans,
 *             investments, insurance, news); null = always sent (security
 *             notices and answers to the customer's own request).
 *   sms       also sent by SMS (critical: withdrawals, transfers, bill codes,
 *             card security, overdue).
 *   email     also sent by e-mail (receipts and alerts).
 *
 * The {placeholders} of a panel type's default text are exactly the values
 * the panel fills; a template that uses another one is flagged on the
 * Notifications page.
 */
class NotificationTypes
{
    public const MODULES = [
        'kyc' => 'Identity verification', 'wallet' => 'Wallet', 'transfers' => 'Transfers and bills', 'cards' => 'Virtual cards',
        'savings' => 'Savings', 'tontines' => 'Tontines', 'loans' => 'Loans', 'investments' => 'Investments',
        'insurance' => 'Insurance', 'financing' => 'Project financing', 'intelligence' => 'Opportunities and chat', 'support' => 'Support',
    ];

    /** type => [module, by, category, sms, email, subject, message] */
    private const TYPES = [
        'kyc_submitted' => ['kyc', 'app', null, false, false, 'Verification received', 'We received your documents. Our team will review them shortly.'],
        'kyc_additional_info_required' => ['kyc', 'panel', null, false, true, 'More information needed', 'Please update your identity verification: {reason}'],
        'kyc_approved' => ['kyc', 'panel', null, false, true, 'Identity verified', 'Your identity is verified. You can now use Spideli Finance services.'],
        'kyc_rejected' => ['kyc', 'panel', null, false, true, 'Verification not approved', 'Your identity verification was not approved: {reason}. You can submit it again.'],

        'wallet_deposit_received' => ['wallet', 'app', 'payments', false, false, 'Money added', '{amount} was added to your Spideli Finance wallet.'],
        'wallet_withdrawal_completed' => ['wallet', 'panel', 'payments', true, true, 'Withdrawal sent', 'Your withdrawal of {amount} has been sent.'],
        'wallet_withdrawal_failed' => ['wallet', 'panel', 'payments', true, true, 'Withdrawal refunded', 'Your withdrawal of {amount} could not be sent and was refunded to your wallet: {reason}'],

        'transfer_sent' => ['transfers', 'panel', 'payments', true, true, 'Money sent', 'You sent {amount} to {recipientName}.'],
        'transfer_received' => ['transfers', 'app', 'payments', false, false, 'Money received', 'You received {amount} from {senderName}.'],
        'transfer_failed' => ['transfers', 'app', 'payments', false, false, 'Transfer failed', 'Your transfer of {amount} to {recipientName} failed: {reason}'],
        'transfer_reversed' => ['transfers', 'panel', 'payments', true, true, 'Transfer refunded', 'Your transfer of {amount} to {recipientName} could not be delivered and was refunded.'],
        'bill_paid' => ['transfers', 'panel', 'payments', false, true, 'Bill paid', 'You paid {amount} to {billerName} for account {account}.'],
        'bill_token' => ['transfers', 'panel', 'payments', true, true, 'Your code', 'Your {billerName} code: {token}'],
        'bill_failed' => ['transfers', 'app', 'payments', false, false, 'Bill payment failed', 'Your payment of {amount} to {billerName} failed: {reason}'],
        'bill_reversed' => ['transfers', 'panel', 'payments', true, true, 'Bill payment refunded', 'Your payment of {amount} to {billerName} could not be completed and was refunded.'],

        'card_created' => ['cards', 'cards', 'payments', false, true, 'Your virtual card is ready', 'Your virtual card ending in {last4} is ready to use.'],
        'card_creation_failed' => ['cards', 'cards', 'payments', false, true, 'Card not created', 'Your virtual card could not be created: {reason}. Any fee paid will be refunded.'],
        'card_funded' => ['cards', 'cards', 'payments', false, true, 'Card topped up', '{amount} was added to your card ending in {last4}.'],
        'card_funding_failed' => ['cards', 'cards', 'payments', false, true, 'Card top-up failed', 'Your top-up of {amount} to the card ending in {last4} did not go through.'],
        'card_transaction' => ['cards', 'cards', 'payments', false, false, 'Card payment', '{amount} paid at {merchantName} with your card ending in {last4}.'],
        'card_transaction_declined' => ['cards', 'cards', 'payments', true, true, 'Card payment declined', 'A payment of {amount} at {merchantName} was declined: {reason}'],
        'card_refund' => ['cards', 'cards', 'payments', false, true, 'Card refund', '{merchantName} refunded {amount} to your card ending in {last4}.'],
        'card_withdrawal_completed' => ['cards', 'cards', 'payments', false, true, 'Card balance moved', '{amount} was moved from your card ending in {last4}.'],
        'card_expiring' => ['cards', 'cards', 'payments', false, true, 'Card expiring soon', 'Your card ending in {last4} expires on {expiry}. Renew it to keep paying.'],
        'card_renewed' => ['cards', 'cards', 'payments', false, true, 'Card renewed', 'Your new virtual card ending in {last4} is ready.'],
        'card_frozen' => ['cards', 'cards', null, true, true, 'Card frozen', 'Your card ending in {last4} is frozen. If you did not do this, contact support.'],
        'card_unfrozen' => ['cards', 'cards', null, true, true, 'Card unfrozen', 'Your card ending in {last4} is active again. If you did not do this, freeze it and contact support.'],
        'card_details_viewed' => ['cards', 'cards', null, true, true, 'Card details viewed', 'The details of your card ending in {last4} were shown at {time}. If this was not you, freeze the card and contact support.'],
        'card_limits_updated' => ['cards', 'cards', null, true, true, 'Card limits changed', 'The limits of your card ending in {last4} were changed. If you did not do this, contact support.'],
        'card_limits_rejected' => ['cards', 'cards', null, true, true, 'Card limits not changed', 'The new limits of your card ending in {last4} were not applied: {reason}'],
        'card_terminated' => ['cards', 'cards', null, true, true, 'Card deleted', 'Your card ending in {last4} was deleted. {amount} was returned to your wallet.'],

        'savings_invite' => ['savings', 'app', 'groups', false, false, 'Savings goal invitation', '{inviterName} invited you to save together for "{goalName}".'],
        'savings_contribution_received' => ['savings', 'app', 'groups', false, false, 'Savings received', '{amount} was added to "{goalName}".'],
        'savings_contribution_failed' => ['savings', 'app', 'groups', false, false, 'Savings payment failed', 'Your payment of {amount} to "{goalName}" did not go through.'],
        'savings_contribution_due' => ['savings', 'panel', 'groups', false, true, 'Savings reminder', 'Your contribution of {amount} to "{goalName}" is due on {dueDate}.'],
        'savings_goal_completed' => ['savings', 'panel', 'groups', false, true, 'Goal reached', 'Congratulations! "{goalName}" has reached its target.'],
        'savings_withdrawal_completed' => ['savings', 'panel', 'groups', false, true, 'Withdrawal completed', '{amount} was withdrawn from "{goalName}".'],
        'savings_withdrawal_rejected' => ['savings', 'panel', 'groups', false, true, 'Withdrawal not possible', 'Your withdrawal of {amount} from "{goalName}" was refused: {reason}'],

        'tontine_invite' => ['tontines', 'app', 'groups', false, false, 'Tontine invitation', '{inviterName} invited you to join the tontine "{tontineName}".'],
        'tontine_started' => ['tontines', 'app', 'groups', false, false, 'Tontine started', 'The tontine "{tontineName}" has started. First contribution due on {firstDueDate}.'],
        'tontine_contribution_due' => ['tontines', 'panel', 'groups', false, true, 'Tontine reminder', 'Your contribution of {amount} to "{tontineName}" is due on {dueDate}.'],
        'tontine_contribution_received' => ['tontines', 'app', 'groups', false, false, 'Contribution received', 'Your contribution of {amount} to "{tontineName}" was received.'],
        'tontine_contribution_overdue' => ['tontines', 'panel', 'groups', true, true, 'Contribution overdue', 'Your contribution of {amount} to "{tontineName}" is overdue.'],
        'tontine_payout_pending_approval' => ['tontines', 'app', 'groups', false, false, 'Payout to approve', 'The payout of {amount} to {beneficiaryName} in "{tontineName}" is waiting for your approval.'],
        'tontine_payout_paid' => ['tontines', 'panel', 'groups', false, true, 'Tontine payout', '{amount} was paid out from "{tontineName}".'],
        'tontine_dispute_raised' => ['tontines', 'app', 'groups', false, false, 'New dispute', 'A dispute was raised in "{tontineName}": {subject}'],

        'loan_submitted' => ['loans', 'panel', 'loans', false, true, 'Loan request received', 'Your loan request of {amount} was received. We will review it shortly.'],
        'loan_vote_requested' => ['loans', 'panel', 'loans', false, false, 'Vote on a loan request', '{borrowerName} asks {groupName} for a loan of {amount}. Vote before {votingEndsAt}.'],
        'loan_approved' => ['loans', 'panel', 'loans', false, true, 'Loan approved', 'Your loan request of {amount} was approved by your group.'],
        'loan_rejected' => ['loans', 'panel', 'loans', false, true, 'Loan not approved', 'Your loan request of {amount} was not approved: {reason}'],
        'loan_funding_open' => ['loans', 'panel', 'loans', false, false, 'Loan open for funding', '{borrowerName} in {groupName} needs {amount}. You can lend part of it.'],
        'loan_funded' => ['loans', 'app', 'loans', false, false, 'Loan fully funded', 'Your loan of {amount} is fully funded. Please accept the agreement.'],
        'loan_agreement_ready' => ['loans', 'app', 'loans', false, false, 'Agreement to accept', 'Your loan agreement for {amount} is ready. Read and accept it to receive the money.'],
        'loan_disbursed' => ['loans', 'app', 'loans', false, false, 'Loan paid out', 'The loan of {amount} was paid out.'],
        'loan_repayment_due' => ['loans', 'panel', 'loans', false, true, 'Repayment reminder', 'Your loan repayment of {amount} is due on {dueDate}.'],
        'loan_repayment_received' => ['loans', 'app', 'loans', false, false, 'Repayment received', 'Your repayment of {amount} was received.'],
        'loan_repayment_overdue' => ['loans', 'panel', 'loans', true, true, 'Repayment overdue', 'Your loan repayment of {amount} is overdue.'],
        'loan_lender_repayment' => ['loans', 'panel', 'loans', false, true, 'Repayment received', 'You received {amount} from {borrowerName}\'s loan repayment.'],
        'loan_repaid' => ['loans', 'app', 'loans', false, false, 'Loan repaid', 'The loan of {amount} is fully repaid.'],

        'investment_subscription_received' => ['investments', 'app', 'investments', false, false, 'Investment request received', 'We received your request to invest {amount} in {fundName}.'],
        'investment_payment_confirmed' => ['investments', 'panel', 'investments', false, true, 'Payment confirmed', 'Your payment of {amount} for {fundName} is confirmed. Units are bought at the next unit price.'],
        'investment_payment_failed' => ['investments', 'app', 'investments', false, false, 'Investment payment failed', 'Your payment of {amount} for {fundName} did not go through.'],
        'investment_units_allocated' => ['investments', 'panel', 'investments', false, true, 'Units allocated', '{units} units of {fundName} were allocated to you at {navPerUnit} per unit.'],
        'investment_subscription_rejected' => ['investments', 'panel', 'investments', false, true, 'Investment not accepted', 'Your investment of {amount} in {fundName} was not accepted: {reason}'],
        'investment_redemption_received' => ['investments', 'app', 'investments', false, false, 'Redemption request received', 'We received your redemption request for {fundName}.'],
        'investment_redemption_processed' => ['investments', 'panel', 'investments', false, true, 'Redemption processed', 'Your redemption from {fundName} was processed. {amount} will be paid out by {settlementDate}.'],
        'investment_redemption_settled' => ['investments', 'panel', 'investments', false, true, 'Redemption paid out', '{amount} from {fundName} was paid out.'],
        'investment_redemption_rejected' => ['investments', 'panel', 'investments', false, true, 'Redemption not accepted', 'Your redemption from {fundName} was not accepted: {reason}'],
        'investment_report_available' => ['investments', 'panel', 'investments', false, true, 'New investment statement', 'Your investment statement for {period} is available.'],
        'investment_terms_updated' => ['investments', 'panel', 'investments', false, true, 'Fund terms updated', 'The terms of {fundName} changed. Read them before your next investment.'],

        'insurance_quote_ready' => ['insurance', 'panel', 'insurance', false, true, 'Your quote is ready', 'Your {productName} quote is ready: {amount}. It is valid until {validUntil}.'],
        'insurance_quote_rejected' => ['insurance', 'panel', 'insurance', false, true, 'Quote not offered', 'The insurer cannot offer {productName}: {reason}'],
        'insurance_policy_active' => ['insurance', 'panel', 'insurance', false, true, 'Your policy is active', 'Your {productName} policy {policyNumber} is active from {coverStart}.'],
        'insurance_policy_rejected' => ['insurance', 'panel', 'insurance', false, true, 'Policy not accepted', 'The insurer did not accept your {productName} policy: {reason}. Your payment will be refunded.'],
        'insurance_premium_due' => ['insurance', 'panel', 'insurance', false, true, 'Premium reminder', 'Your {productName} premium of {amount} is due on {dueDate}.'],
        'insurance_premium_overdue' => ['insurance', 'panel', 'insurance', true, true, 'Premium overdue', 'Your {productName} premium of {amount} is overdue. Pay it to stay covered.'],
        'insurance_policy_lapsed' => ['insurance', 'panel', 'insurance', true, true, 'You are no longer covered', 'Your {productName} policy lapsed because a premium was not paid.'],
        'insurance_renewal_due' => ['insurance', 'panel', 'insurance', false, true, 'Renewal due', 'Your {productName} policy renews for {amount}. Renew before {renewalDate}.'],
        'insurance_policy_renewed' => ['insurance', 'panel', 'insurance', false, true, 'Policy renewed', 'Your {productName} policy is renewed until {coverEnd}.'],
        'insurance_policy_cancelled' => ['insurance', 'panel', 'insurance', false, true, 'Policy cancelled', 'Your {productName} policy is cancelled.'],
        'insurance_claim_received' => ['insurance', 'panel', 'insurance', false, true, 'Claim received', 'We received your {productName} claim of {amount} and sent it to the insurer.'],
        'insurance_claim_info_requested' => ['insurance', 'panel', 'insurance', false, true, 'More information needed', 'The insurer needs more information about your {productName} claim: {reason}'],
        'insurance_claim_approved' => ['insurance', 'panel', 'insurance', false, true, 'Claim approved', 'Your {productName} claim was approved for {amount}.'],
        'insurance_claim_rejected' => ['insurance', 'panel', 'insurance', false, true, 'Claim not approved', 'Your {productName} claim was not approved: {reason}'],
        'insurance_claim_paid' => ['insurance', 'panel', 'insurance', false, true, 'Claim paid', '{amount} was paid for your {productName} claim.'],

        'financing_submitted' => ['financing', 'app', null, false, false, 'Application received', 'Your financing application for {projectName} was received.'],
        'financing_under_review' => ['financing', 'panel', null, false, true, 'Application under review', 'Your financing application for {projectName} is being reviewed.'],
        'financing_info_required' => ['financing', 'panel', null, false, true, 'More information needed', 'The reviewers need more information about {projectName}: {reason}'],
        'financing_document_rejected' => ['financing', 'panel', null, false, true, 'Document to replace', 'Please replace "{documentName}" in your application for {projectName}: {note}'],
        'financing_approved' => ['financing', 'panel', null, false, true, 'Financing approved', 'Your application for {projectName} was approved for {amount}.'],
        'financing_rejected' => ['financing', 'panel', null, false, true, 'Financing not approved', 'Your application for {projectName} was not approved: {reason}'],
        'financing_funded' => ['financing', 'panel', null, false, true, 'Financing paid', '{amount} was paid for {projectName}.'],
        'financing_closed' => ['financing', 'panel', null, false, true, 'Application closed', 'Your financing application for {projectName} is closed.'],

        'intelligence_new_match' => ['intelligence', 'panel', 'investments', false, false, 'New opportunity for you', '{title} matches your investor preferences ({score}%).'],
        'intelligence_interest_received' => ['intelligence', 'panel', 'investments', false, true, 'An investor is interested', '{investorName} is interested in {title}.'],
        'finance_chat_message' => ['intelligence', 'app', 'groups', false, false, '{chatName}', '{senderName}: {message}'],

        'support_ticket_resolved' => ['support', 'panel', null, false, true, 'Your request is resolved', '"{subject}": {resolution}'],
    ];

    /** Every type as [type => [module, by, category, sms, email, subject, message] keyed by name]. */
    public static function all()
    {
        $out = [];
        foreach (self::TYPES as $type => [$module, $by, $category, $sms, $email, $subject, $message]) {
            $out[$type] = compact('module', 'by', 'category', 'sms', 'email', 'subject', 'message');
        }
        return $out;
    }

    public static function get($type)
    {
        return self::all()[$type] ?? null;
    }

    /**
     * The preference category of a type (null = always sent). A type not in
     * the list counts by its prefix; anything else is payments.
     */
    public static function category($type)
    {
        if (array_key_exists($type, self::TYPES)) {
            return self::TYPES[$type][2];
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
