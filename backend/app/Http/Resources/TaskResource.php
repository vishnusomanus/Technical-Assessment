<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->formatDate($this->due_date),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }

    /**
     * Format date field
     */
    private function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            return $date;
        }

        return $date->format('Y-m-d');
    }

    /**
     * Format datetime field
     */
    private function formatDateTime($datetime): ?string
    {
        if (!$datetime) {
            return null;
        }

        if (is_string($datetime)) {
            return $datetime;
        }

        return $datetime->format('Y-m-d H:i:s');
    }
}
