<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A Spideli Finance notice by e-mail (spec 6.14): the template's title and
 * filled text, escaped (the values can hold what a customer typed).
 */
class FinanceNotice extends Mailable
{
    use Queueable, SerializesModels;

    public $noticeTitle;
    public $noticeBody;

    public function __construct($title, $body)
    {
        $this->noticeTitle = (string)$title;
        $this->noticeBody = (string)$body;
    }

    public function build()
    {
        return $this->subject($this->noticeTitle !== '' ? $this->noticeTitle : 'Spideli Finance')
            ->view('emails.finance_notice')
            ->text('emails.finance_notice_text');
    }
}
