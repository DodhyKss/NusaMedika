<?php

namespace App\Http\Controllers\EMR\EmrDashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmrDashboardController extends Controller
{
    public function index()
    {
        return view('moduls.emr_dashboard.dashboard_pasien');
    }
}
