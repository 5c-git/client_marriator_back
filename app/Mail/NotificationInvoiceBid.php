<?php

namespace App\Mail;

use App\Models\User;
use App\Services\User\UserDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationInvoiceBid extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $link;

    public function __construct()
    {
        $this->link = env('FRONT_URL', '');
    }

    public function build()
    {
        return $this
            ->subject('Новая заявка на платформе временной занятости “Marriator”')
            ->view('emails.notificationInvoiceBid');
    }
}
