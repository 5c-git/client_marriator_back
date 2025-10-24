<?php

namespace App\Http\Resources\Document;

use App\Models\Document\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
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
            'file_path' => $this->file_path?Storage::url($this->file_path):null,
            'file_name' => $this->file_name,
            'status' => $this->status,
            'status_signature' => $this->status_signature,
            'date_signature' => $this->date_signature,
            'file_path_signed' => $this->file_path_signed?Storage::url($this->file_path_signed):null
        ];
    }
}
