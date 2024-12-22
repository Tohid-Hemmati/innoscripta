<?php

namespace App\Contracts;

use App\Http\Requests\RegisterRequest;
use App\Models\User;

interface UserRepositoryInterface
{
    public function createUser(RegisterRequest $data): User;
}
