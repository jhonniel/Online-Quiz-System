<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
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
