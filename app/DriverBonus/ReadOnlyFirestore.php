<?php

namespace App\DriverBonus;

use App\Finance\Firestore;

/**
 * The Firestore client for `drivers:bonuses --dry-run`: reads as usual, and
 * refuses every write, so a dry run can never change live data.
 */
class ReadOnlyFirestore extends Firestore
{
    public function transaction(callable $work, $attempts = 5)
    {
        throw new \LogicException('Dry run: no transaction may be written.');
    }

    public function batch(array $writes)
    {
        throw new \LogicException('Dry run: nothing may be written.');
    }

    public function commit(array $writes, $transaction = null)
    {
        throw new \LogicException('Dry run: nothing may be written.');
    }
}
