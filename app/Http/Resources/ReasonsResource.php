<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Fields\Directory\Reasons
 */
class ReasonsResource extends JsonResource
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
        $return = [
            'id' => $this->id,
            'value' => $this->name,
            'amount' => $this->amount,
        ];

        if(!empty($this->pivot) && !empty($this->pivot->count)){
            $return['count'] = $this->pivot->count;
        }

        return $return;
    }
}
