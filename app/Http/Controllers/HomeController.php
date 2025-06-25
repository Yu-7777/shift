<?php

namespace App\Http\Controllers;

use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        $groups = $user->getShiftGroups();

        return view('home', compact('user', 'groups'));
    }
}
