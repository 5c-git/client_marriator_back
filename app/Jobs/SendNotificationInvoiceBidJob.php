<?php

namespace App\Jobs;

use App\Models\Fields\Directory\Counterparty;
use App\Models\User;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use App\Services\Register\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mpdf\Tag\Em;

class SendNotificationInvoiceBidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $userId)
    {
        $this->onQueue('notification');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()->where('id',$this->userId)->first();
        /** @var  $user User */
        $setting = $user->settings;
        if($setting && $setting->notification_new_bids) {
            EmailService::sendNotificationInvoiceBid($user);
        }
    }
}
