<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        $groups = $user->groups();
        return view('home', compact('user', 'groups'));
    }
}
