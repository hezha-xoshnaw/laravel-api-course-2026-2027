<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(
                isset($this->resource['id']),
                fn () => $this->resource['id'],
            ),
            'title' => $this->resource['title'],
            'status' => $this->resource['status'],
        ];
    }
}
