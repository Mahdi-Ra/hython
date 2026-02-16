<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $urgentLetters = Letter::query()->where('priority', 'urgent')->get();
        $urgentTasks = Task::query()->where('priority', 'urgent')->get();

        return view('dashboard', compact('urgentLetters', 'urgentTasks'));
    }
}
