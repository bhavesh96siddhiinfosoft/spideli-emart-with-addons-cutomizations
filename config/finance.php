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
];
