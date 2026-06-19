<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Modul;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }
}
