<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    public function index(Group $group)
    {
        $members = $group->members();
        return view('groups.index', compact('group', 'members'));
    }
}
