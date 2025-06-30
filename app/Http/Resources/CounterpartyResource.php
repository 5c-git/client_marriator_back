<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use \Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Fields\Directory\Counterparty
 */
class CounterpartyResource extends JsonResource
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
            'name' => $this->name,
            'ogrn' => $this->ogrn,
            'legal_address' => $this->legal_address,
            'legal_email' => $this->legal_email,
        ];
    }
}
