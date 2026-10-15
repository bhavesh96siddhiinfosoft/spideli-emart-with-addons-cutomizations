<?php

namespace App\Finance;

/** An error returned by the Firestore API (status such as ABORTED, NOT_FOUND, ALREADY_EXISTS). */
class FirestoreError extends \RuntimeException
{
    public $status;

    public function __construct($status, $message, $httpCode = 0)
    {
        $this->status = $status;
        parent::__construct('Firestore ' . $status . ': ' . $message, (int)$httpCode);
    }
}
