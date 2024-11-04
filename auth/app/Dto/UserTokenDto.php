<?php

namespace App\Dto;

use App\Models\User;
use App\Traits\MakeableTrait;

final readonly class UserTokenDto
{
    use MakeableTrait;

    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public string $tokenType = 'Bearer',
        public ?User $user = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
        ];

        if ($this->user) {
            $data['user'] = $this->user;
        }

        return $data;
    }
}
