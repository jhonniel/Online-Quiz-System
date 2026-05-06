<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    protected function authUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    protected function requireAuthUser(): User
    {
        $user = $this->authUser();
        if ($user === null) {
            abort(403);
        }

        return $user;
    }
}
