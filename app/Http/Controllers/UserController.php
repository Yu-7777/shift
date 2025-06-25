<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // ユーザーが所属しているシフトグループの一覧を取得
    public function showGroups(User $user)
    {
        $groups = $user->getShiftGroups();

        return view('users.groups', compact('user', 'groups'));
    }
}
