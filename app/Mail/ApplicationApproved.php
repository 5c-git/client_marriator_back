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

class ApplicationApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $fullName;
    public string $loginUrl;

    public function __construct(User $user)
    {
        $this->fullName = (new UserDataService())->getName($user);
        $this->loginUrl = env('FRONT_URL', '').'/signin/phone';

        $this->onQueue('emailMessage');
    }

    public function build()
    {
        return $this
            ->subject('Ваша анкета одобрена')
            ->view('emails.applicationApproved');
    }
}
