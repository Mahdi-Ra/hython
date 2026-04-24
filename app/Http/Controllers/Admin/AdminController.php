<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canAccessManagementPanel(), 403);

        return view('admin.index');
    }
}
