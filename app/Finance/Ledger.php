<?php

namespace App\Finance;

/**
 * The posting helper of spec 5.2: double-entry postings written inside a
 * Firestore transaction.
 *
 * plan() only reads and checks, write() only writes, so an action that makes
 * several postings plans all of them first, then writes them all (Firestore
 * transactions need every read before the first write). $state carries the
 * running balance of each account across the plans of one transaction.
 *
 * The rules of the ledger hold for the panel too: never edit or delete a
 * posting, every balance change has its posting, a wallet never goes below 0,
 * only platform_{CUR} may.
 */
class Ledger
{
    public static function newState()
    {
        return ['accounts' => [], 'created' => []];
    }

    public static function platformId($currency)
    {
        return 'platform_' . $currency;
    }

    /** [uid, CUR] from wallet_{uid}_{CUR}, or null. */
    public static function parseWalletId($id)
    {
        if (!preg_match('/^wallet_(.+)_([A-Z]{3})$/', $id, $m)) {
            return null;
        }
        return [$m[1], $m[2]];
    }

    public function plan(Transaction $t, array &$state, $type, $cur, $from, $to, $amount, $fee,
                         array $userIds, array $source, $description, $reversalOf = null)
    {
        if (!is_int($amount) || $amount <= 0 || !is_int($fee) || $fee < 0) {
            throw new FinanceException('invalid_amount', 'Amounts must be positive whole numbers of minor units.');
        }
        if ($from === $to) {
            throw new FinanceException('same_account', 'The two accounts of a posting must differ.');
        }
        if (!Money::isCurrency($cur)) {
            throw new FinanceException('invalid_currency', 'Invalid currency.');
        }

        $platform = self::platformId($cur);
        $lines = [
            ['accountId' => $from, 'direction' => 'debit', 'amount' => $amount + $fee],
            ['accountId' => $to, 'direction' => 'credit', 'amount' => $amount],
        ];
        if ($fee > 0 && $from !== $platform && $to !== $platform) {
            $lines[] = ['accountId' => $platform, 'direction' => 'credit', 'amount' => $fee];
        }

        foreach ($lines as $l) {
            $id = $l['accountId'];
            if (!array_key_exists($id, $state['accounts'])) {
                $snap = $t->get('financial_accounts/' . $id);
                $state['accounts'][$id] = $snap ? $snap['data'] : null;
            }
            $acc = $state['accounts'][$id];

            if ($acc === null) {
                $wallet = self::parseWalletId($id);
                // Only a wallet may be created, only by a credit, only in its own currency.
                if ($l['direction'] === 'debit' || $wallet === null || $wallet[1] !== $cur) {
                    throw new FinanceException('account_missing', "The account $id does not exist.", ['account' => $id]);
                }
            } else {
                if (($acc['status'] ?? '') !== 'active') {
                    throw new FinanceException('account_not_active', "The account $id is not active.", ['account' => $id]);
                }
                if (($acc['currency'] ?? '') !== $cur) {
                    throw new FinanceException('currency_mismatch', "The account $id is not in $cur.", ['account' => $id]);
                }
            }

            $delta = $l['direction'] === 'debit' ? -$l['amount'] : $l['amount'];
            $after = (int)($acc['balance'] ?? 0) + $delta;
            if ($after < 0 && ($acc['kind'] ?? 'wallet') !== 'platform') {
                throw new FinanceException('insufficient_funds', "The account $id does not hold enough money.", ['account' => $id]);
            }
            $state['accounts'][$id] = ($acc ?? ['kind' => 'wallet', 'currency' => $cur, 'status' => 'active', 'new' => true]);
            $state['accounts'][$id]['balance'] = $after;
        }

        return compact('type', 'cur', 'lines', 'userIds', 'source', 'description', 'reversalOf');
    }

    /** Writes one planned posting; returns its txId. */
    public function write(Transaction $t, array &$state, array $p, $adminId)
    {
        $txId = Money::newTxId();
        $doc = [
            'id' => $txId,
            'type' => $p['type'],
            'status' => 'posted',
            'reference' => Money::newReference(),
            'currency' => $p['cur'],
            'lines' => $p['lines'],
            'userIds' => array_values(array_unique(array_filter($p['userIds']))),
            'accountIds' => array_column($p['lines'], 'accountId'),
            'source' => $p['source'],
            'funding' => 'wallet',
            'description' => $p['description'],
            'createdBy' => 'admin:' . $adminId,
            'createdAt' => Value::serverTimestamp(),
        ];
        if ($p['reversalOf']) {
            $doc['reversalOf'] = $p['reversalOf'];
        }
        $t->create('ledger_transactions/' . $txId, $doc);

        foreach ($p['lines'] as $l) {
            $id = $l['accountId'];
            $delta = $l['direction'] === 'debit' ? -$l['amount'] : $l['amount'];
            if (!empty($state['accounts'][$id]['new']) && empty($state['created'][$id])) {
                [$uid, $cur] = self::parseWalletId($id);
                $t->create('financial_accounts/' . $id, [
                    'id' => $id, 'kind' => 'wallet', 'userId' => $uid, 'currency' => $cur,
                    'balance' => $delta, 'normalSide' => 'credit', 'status' => 'active', 'lastTxId' => $txId,
                    'createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp(),
                ]);
                $state['created'][$id] = true;   // a second posting in this transaction updates it
            } else {
                $t->update('financial_accounts/' . $id, [
                    'balance' => Value::increment($delta),
                    'lastTxId' => $txId,
                    'updatedAt' => Value::serverTimestamp(),
                ]);
            }
        }
        return ['txId' => $txId, 'reference' => $doc['reference']];
    }

    /**
     * Whether a posting has the shape every real posting has (2 or 3 lines,
     * the first the only debit, balanced, positive whole amounts). Demo
     * records written by hand do not, and cannot be reversed.
     */
    public static function isWellFormed(array $tx)
    {
        $lines = $tx['lines'] ?? null;
        if (!is_array($lines) || count($lines) < 2 || count($lines) > 3) {
            return false;
        }
        $credits = 0;
        foreach ($lines as $i => $l) {
            if (!isset($l['accountId'], $l['direction'], $l['amount']) || !is_int($l['amount']) || $l['amount'] <= 0) {
                return false;
            }
            if ($l['direction'] !== ($i === 0 ? 'debit' : 'credit')) {
                return false;
            }
            if ($i > 0) {
                $credits += $l['amount'];
            }
        }
        return $lines[0]['amount'] === $credits;
    }

    /**
     * reverse() of spec 5.2: gives the main amount back from the credited
     * account to the debited one, and a 3-line posting's fee back from the
     * platform account. The original becomes status "reversed".
     * Returns [['txId' => .., 'reference' => ..], ...].
     */
    public function reverse(Transaction $t, $txId, $adminId, $reason, &$original = null)
    {
        $snap = $t->get('ledger_transactions/' . $txId);
        if (!$snap) {
            throw new FinanceException('posting_missing', 'This posting does not exist.');
        }
        $orig = $original = $snap['data'];
        if (($orig['status'] ?? '') === 'reversed') {
            throw new FinanceException('already_reversed', 'This posting is already reversed.');
        }
        if (($orig['type'] ?? '') === 'reversal') {
            throw new FinanceException('not_reversible', 'A reversal cannot be reversed. Make a manual adjustment instead.');
        }
        if (!self::isWellFormed($orig)) {
            throw new FinanceException('not_reversible', 'This record is not a well-formed posting (2 or 3 balanced lines) and cannot be reversed.');
        }

        $debit = $orig['lines'][0];
        $credit = $orig['lines'][1];
        $state = self::newState();
        $userIds = $orig['userIds'] ?? [];
        $source = $orig['source'] ?? ['kind' => 'ledger_transactions', 'id' => $txId];

        $plans = [$this->plan($t, $state, 'reversal', $orig['currency'], $credit['accountId'], $debit['accountId'],
            $credit['amount'], 0, $userIds, $source, $reason, $txId)];
        if (isset($orig['lines'][2])) {
            $plans[] = $this->plan($t, $state, 'reversal', $orig['currency'], $orig['lines'][2]['accountId'],
                $debit['accountId'], $orig['lines'][2]['amount'], 0, $userIds, $source, $reason, $txId);
        }

        $written = [];
        foreach ($plans as $p) {
            $written[] = $this->write($t, $state, $p, $adminId);
        }
        $t->update('ledger_transactions/' . $txId, [
            'status' => 'reversed',
            'reversedBy' => array_column($written, 'txId'),
            'reversedAt' => Value::serverTimestamp(),
        ]);
        return $written;
    }

    /**
     * The wallet a manual adjustment uses: the user's existing active wallet
     * in that currency (an older one keeps its own id), else wallet_{uid}_{CUR}.
     */
    public function walletIdFor(Transaction $t, $uid, $cur)
    {
        $found = $t->query('financial_accounts', [
            ['userId', '==', $uid], ['kind', '==', 'wallet'], ['currency', '==', $cur], ['status', '==', 'active'],
        ], [], 1);
        return $found ? $found[0]['id'] : 'wallet_' . $uid . '_' . $cur;
    }

    /**
     * Manual adjustment (spec 6.5.3) between a user's wallet and platform_{CUR}.
     * $direction "credit" puts money in the wallet, "debit" takes it out.
     */
    public function adjust(Transaction $t, $uid, $cur, $direction, $amount, $reason, $adminId, array $source)
    {
        $wallet = $this->walletIdFor($t, $uid, $cur);
        $platform = self::platformId($cur);
        [$from, $to] = $direction === 'credit' ? [$platform, $wallet] : [$wallet, $platform];
        $state = self::newState();
        $plan = $this->plan($t, $state, 'manual_adjustment', $cur, $from, $to, $amount, 0, [$uid], $source, $reason);
        $written = $this->write($t, $state, $plan, $adminId);
        return $written + ['walletId' => $wallet];
    }
}
