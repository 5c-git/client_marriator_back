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
            'id' => $this->id,
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'report' => $this->report,
            'dayActivityId' => $this->dayActivity,
            'status' => $this->status->value,
            'hours' => $this->hours
        ];
    }
}
