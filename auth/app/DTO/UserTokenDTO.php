<?php

namespace App\DTO;

use App\Models\User;

readonly class UserTokenDTO
{
    /**
     * @param string $accessToken
     * @param int $expiresIn
     * @param string $tokenType
     * @param User|null $user
     */
    public function __construct
    (
        public string $accessToken,
        public int    $expiresIn,
        public string $tokenType = 'Bearer',
        public ?User  $user = null,
    )
    {
        //
    }

    /**
     * @return array
     */
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

