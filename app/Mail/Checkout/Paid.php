<?php

namespace App\Mail\Checkout;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Paid extends Mailable
{
    use Queueable, SerializesModels;

    private $checkout;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($checkout)
    {
        $this->checkout = $checkout; //ini untuk mengambil data checkout
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Your Transaction Has Been Confirmed")->markdown('emails.checkout.paid', [
            'checkout'=> $this->checkout
        ]);
    }
}
