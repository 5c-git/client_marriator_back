<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct($resource = [])
    {
        parent::__construct($resource);
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(404);

        parent::withResponse($request, $response);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->resource === []) {
            return [
                'error' => true,
            ];
        }

        return parent::toArray($request);
    }
}
