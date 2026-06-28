<?php

namespace Modules\YandexSmena\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\YandexSmena\Models\SmenaFavoriteWorker;

class SmenaWorkerInteractionService
{
    private const ENTITY_TYPE = 'worker';

    public function __construct(private readonly YandexSmenaEventPublisher $publisher)
    {
    }

    public function blockWorker(
        string $workerId,
        string $reason,
        string $blockType,
        ?Carbon $blockedUntil = null,
        ?string $siteId = null,
        ?string $professionId = null,
        ?string $comment = null
    ): void {
        $this->ensureValidReason($reason, [
            'incident',
            'invalid_documents',
            'intoxicated',
            'spoof_account',
            'no_mandatory_training',
            'low_performance',
            'low_qualification',
            'other',
        ]);

        if (! in_array($blockType, ['temporary', 'permanent'], true)) {
            throw new InvalidArgumentException("Invalid block_type '{$blockType}'");
        }

        if ($blockType === 'temporary' && $blockedUntil === null) {
            throw new InvalidArgumentException('blocked_until is required for temporary block');
        }

        $payload = [
            'reason' => $reason,
            'block_type' => $blockType,
        ];

        if ($blockedUntil !== null) {
            $payload['blocked_until'] = $blockedUntil->copy()->utc()->toIso8601ZuluString();
        }

        if ($siteId !== null && $siteId !== '') {
            $payload['site_id'] = $siteId;
        }

        if ($professionId !== null && $professionId !== '') {
            $payload['profession_id'] = $professionId;
        }

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish('provider.worker.block', self::ENTITY_TYPE, $workerId, $payload);
    }

    public function unblockWorker(
        string $workerId,
        ?string $siteId = null,
        ?string $professionId = null,
        ?string $comment = null
    ): void {
        $payload = [];

        if ($siteId !== null && $siteId !== '') {
            $payload['site_id'] = $siteId;
        }

        if ($professionId !== null && $professionId !== '') {
            $payload['profession_id'] = $professionId;
        }

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish('provider.worker.unblock', self::ENTITY_TYPE, $workerId, $payload);
    }

    public function likeWorker(string $workerId, string $siteId, ?string $comment = null): void
    {
        $payload = ['site_id' => $siteId];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->recordFavorite($workerId, $siteId, true);

        $this->publisher->publish('provider.worker.like', self::ENTITY_TYPE, $workerId, $payload);
    }

    public function unlikeWorker(string $workerId, string $siteId, ?string $comment = null): void
    {
        $payload = ['site_id' => $siteId];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->recordFavorite($workerId, $siteId, false);

        $this->publisher->publish('provider.worker.unlike', self::ENTITY_TYPE, $workerId, $payload);
    }

    private function recordFavorite(string $workerId, string $siteId, bool $isFavorite): void
    {
        SmenaFavoriteWorker::query()->updateOrCreate(
            [
                'external_worker_id' => $workerId,
                'yandex_smena_site_id' => $this->resolveSiteId($siteId),
            ],
            ['is_favorite' => $isFavorite]
        );
    }

    private function resolveSiteId(string $externalSiteId): ?int
    {
        $site = \Modules\YandexSmena\Models\SmenaSite::query()
            ->where('external_id', $externalSiteId)
            ->first();

        return $site?->id;
    }

    private function ensureValidReason(string $reason, array $allowed): void
    {
        if (! in_array($reason, $allowed, true)) {
            throw new InvalidArgumentException("Invalid reason '{$reason}'. Allowed: ".implode(', ', $allowed));
        }
    }
}
