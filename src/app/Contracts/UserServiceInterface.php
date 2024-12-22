<?php

namespace App\Contracts;

use App\Http\Requests\RegisterRequest;
use App\Models\User;

interface UserServiceInterface
{
    public function registerUser(RegisterRequest $data): User;
}
