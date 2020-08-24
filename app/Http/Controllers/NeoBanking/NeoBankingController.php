<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getDashboardData()
    {
        return "get dashboard";
    }
}
