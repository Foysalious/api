<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dashboard = [
            'notice' => [
                'title' => 'Notice',
                'is_published' => 1,
                'order' => 1,
            ],
            'attendance' => [
                'title' => 'Attendance',
                'is_published' => 1,
                'order' => 2,
            ],
            'approval' => [
                'title' => 'Approval',
                'is_published' => 1,
                'order' => 3,
            ],
            'leave' => [
                'title' => 'Leave',
                'is_published' => 1,
                'order' => 4,
            ],
            'my_team' => [
                'title' => 'My Team',
                'is_published' => 1,
                'order' => 5,
            ],
            'visits' => [
                'title' => 'Visits',
                'is_published' => 1,
                'order' => 6,
            ],
            'support' => [
                'title' => 'Support',
                'is_published' => 1,
                'order' => 7,
            ],
            'phonebook' => [
                'title' => 'Phonebook',
                'is_published' => 1,
                'order' => 8,
            ],
            'payslip' => [
                'title' => 'Payslip',
                'is_published' => 1,
                'order' => 9,
            ],
        ];

        return api_response($request, $dashboard, 200, ['dashboard' => $dashboard]);
    }
}