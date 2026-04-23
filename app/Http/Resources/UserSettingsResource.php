<?php

namespace App\Http\Resources;

use App\Models\Setting;
use App\Models\User\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserSettings
 */
class UserSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'notification_new_bids' => $this->notification_new_bids
        ];
    }
}
