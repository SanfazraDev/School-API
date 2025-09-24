<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Transform the resource into an array with authentication token.
     *
     * @param string $token
     * @param string $tokenType
     * @return array<string, mixed>
     */
    public function withToken(string $token, string $tokenType = 'Bearer'): array
    {
        return array_merge($this->toArray(request()), [
            'access_token' => $token,
            'token_type' => $tokenType,
        ]);
    }
}