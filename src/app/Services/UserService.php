<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class UserService implements UserServiceInterface
{

    public function __construct(protected UserRepositoryInterface $userRepository){}

    public function registerUser(RegisterRequest $data): User
    {
        return $this->userRepository->createUser($data);
    }
}
