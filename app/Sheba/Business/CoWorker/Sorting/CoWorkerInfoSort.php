<?php namespace Sheba\Business\CoWorker\Sorting;

use Illuminate\Http\Request;

class CoWorkerInfoSort
{
    public function sortCoworker($employees, Request $request)
    {
        if ($request->has('sort_by_employee_id')) $employees = $this->sortByEmployeeId($employees, $request->sort_by_employee_id)->values();
        if ($request->has('sort_by_name')) $employees = $this->sortByName($employees, $request->sort_by_name)->values();
        if ($request->has('sort_by_department')) $employees = $this->sortByDepartment($employees, $request->sort_by_department)->values();
        if ($request->has('sort_by_status')) $employees = $this->sortByStatus($employees, $request->sort_by_status)->values();
        return $employees;
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByEmployeeId($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['employee_id']);
        });
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByName($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['profile']['name']);
        });
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByDepartment($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['department']);
        });
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByStatus($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['status']);
        });
    }
}