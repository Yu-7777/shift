<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index')->with(['users' => $users]);
    }

    public function show(User $user)
    {
        return view('users.show')->with(['user' => $user]);
    }
}
