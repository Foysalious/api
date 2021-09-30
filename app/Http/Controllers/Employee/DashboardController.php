<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dashboard = [
            [
                'title' => 'Notice',
                'target_type' => 'notice',
                'is_published' => 1,
                'order' => 1,
            ],
            [
                'title' => 'Attendance',
                'target_type' => 'attendance',
                'is_published' => 1,
                'order' => 2,
            ],
            [
                'title' => 'Approval',
                'target_type' => 'approval',
                'is_published' => 1,
                'order' => 3,
            ],
            [
                'title' => 'Leave',
                'target_type' => 'leave',
                'is_published' => 1,
                'order' => 4,
            ],
            [
                'title' => 'My Team',
                'target_type' => 'my_team',
                'is_published' => 1,
                'order' => 5,
            ],
            [
                'title' => 'Visits',
                'target_type' => 'visits',
                'is_published' => 1,
                'order' => 6,
            ],
            [
                'title' => 'Support',
                'target_type' => 'support',
                'is_published' => 1,
                'order' => 7
            ],
            [
                'title' => 'Phonebook',
                'target_type' => 'phonebook',
                'is_published' => 1,
                'order' => 8
            ],
            [
                'title' => 'Payslip',
                'target_type' => 'payslip',
                'is_published' => 1,
                'order' => 9
            ],
            [
                'title' => 'Policy',
                'target_type' => 'policy',
                'is_published' => 1,
                'order' => 10
            ],
        ];

        return api_response($request, $dashboard, 200, ['dashboard' => $dashboard]);
    }
}