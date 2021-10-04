<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dashboard = [
            [
                'title' => 'Support',
                'target_type' => 'support',
            ],
            [
                'title' => 'Attendance',
                'target_type' => 'attendance',
            ],
            [
                'title' => 'Notice',
                'target_type' => 'notice',
            ],
            [
                'title' => 'Expense',
                'target_type' => 'expense',
            ],
            [
                'title' => 'Leave',
                'target_type' => 'leave',
            ],
            [
                'title' => 'Approval',
                'target_type' => 'approval',
            ],
            [
                'title' => 'Phonebook',
                'target_type' => 'phonebook',
            ],
            [
                'title' => 'Payslip',
                'target_type' => 'payslip',

            ],
        ];

        return api_response($request, $dashboard, 200, ['dashboard' => $dashboard]);
    }
}