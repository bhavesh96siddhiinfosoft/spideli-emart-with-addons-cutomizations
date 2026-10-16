<?php

/*
 * Spideli Finance - panel settings that are not customer-facing (the
 * customer-facing ones are the region's finance_settings documents).
 */
return [
    // Days after an instalment's due date before it is overdue and the
    // region's latePenaltyPercent is added (chosen 17 Oct 2026).
    'loan_grace_days' => 3,

    // A loan still in "funding" or "agreement_pending" after this many days
    // is listed as stuck, for an admin to refund (never automatically).
    'loan_stuck_days' => 14,

    // Reminders go this many days before a due date.
    'reminder_days_before' => 2,

    // Insurance (chosen 16 Oct 2026). A premium becomes "due" this many days
    // before its date and "overdue" after it; still unpaid this many days
    // after its date, the policy lapses.
    'insurance_premium_due_days_before' => 7,
    'insurance_lapse_days' => 30,

    // This many days before coverEnd a policy becomes "renewal_due", at its
    // current yearly premium (an admin may change it before it is paid).
    'insurance_renewal_days_before' => 30,

    // A priced quote can be accepted for this many days (the admin may change
    // it on the quote).
    'insurance_quote_valid_days' => 14,

    // Support tickets: hours to resolve, by priority (chosen 16 Oct 2026).
    // A ticket open longer is shown late.
    'ticket_sla_hours' => ['urgent' => 4, 'high' => 24, 'normal' => 72, 'low' => 168],

    // Finance notices by SMS (the SMS gateway of Settings, when it is on) and
    // by e-mail (the panel's mail settings), as each customer allows them.
    // Set FINANCE_NOTIFY_SMS=false / FINANCE_NOTIFY_EMAIL=false to stop one.
    'notify_sms' => env('FINANCE_NOTIFY_SMS', true),
    'notify_email' => env('FINANCE_NOTIFY_EMAIL', true),
];
