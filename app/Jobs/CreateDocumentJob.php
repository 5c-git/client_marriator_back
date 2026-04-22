<?php

namespace App\Jobs;

use App\Models\Fields\Directory\Counterparty;
use App\Models\User;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user,private readonly Counterparty $counterparty)
    {
        $this->onQueue('createDocument');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new UserDocumentCreatorService();
        $service->createContract($this->user,$this->counterparty);
    }
}
