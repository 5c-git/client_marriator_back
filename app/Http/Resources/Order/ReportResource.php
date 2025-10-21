<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\Report
 */
class ReportResource extends JsonResource
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
            'dateStart' => $this->date_start->timezone('Europe/Moscow')->format('Y-m-d\TH:i:sP'),
            'dateEnd' => $this->date_end->timezone('Europe/Moscow')->format('Y-m-d\TH:i:sP'),
            'report' => $this->report,
            'dayActivityId' => $this->dayActivity,
            'status' => $this->status->value,
            'hours' => $this->hours
        ];
    }
}
