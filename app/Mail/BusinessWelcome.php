<?php

namespace App\Mail;

use App\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BusinessWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $business;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.business.welcome');
    }
}
