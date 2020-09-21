<?php namespace Sheba\Reports\Data;

use App\Models\Job;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use Illuminate\Http\Request;
use Sheba\Reports\ReportData;

class JobStatusChange extends ReportData
{
    public function get(Request $request)
    {
        $jobs = $this->getJobs($request);
        $data = [];
        foreach ($jobs as $job) {
            /** @var Job $job */
            $resource_change_log = $job->updateLogs->sortByDesc('id')->values()->reject(function (JobUpdateLog $log) {
                return !$log->isResourceChangeLog();
            })->first();
            $status_change_info = $job->statusChangeLogs->pluckMultiple(['created_by_name', 'created_at', 'portal_name'], 'to_status')->toArray();
            $data[] = [
                'full_job_id' => $job->fullCode(),
                'status' => $job->status,
                'created_at' => $job->created_at,
                'partner_name' => $job->partnerOrder->partner->name,
                'resource_name' => $job->resource ? $job->resource->name : 'N/A',
                'category' => $job->category_id ? $job->category->name : $job->service->category->name,

                'accepted_date' => isset($status_change_info['Accepted']) ? $status_change_info['Accepted']['created_at']->format('d M Y H:i') : 'N/A',
                'accepted_by' => isset($status_change_info['Accepted']) ? $status_change_info['Accepted']['created_by_name'] : 'N/A',
                'accepted_from' => isset($status_change_info['Accepted']['portal_name']) ? $status_change_info['Accepted']['portal_name'] : 'N/A',

                'assigned_date' => ($resource_change_log) ? $resource_change_log->created_at->format('d M Y H:i') : 'N/A',
                'assigned_by' => ($resource_change_log) ? $resource_change_log->created_by_name : 'N/A',
                'assigned_from' => ($resource_change_log) ? $resource_change_log->portal_name : 'N/A',

                'declined_date' => isset($status_change_info['Declined']) ? $status_change_info['Declined']['created_at']->format('d M Y H:i') : 'N/A',
                'declined_by' => isset($status_change_info['Declined']) ? $status_change_info['Declined']['created_by_name'] : 'N/A',
                'declined_from' => isset($status_change_info['Declined']['portal_name']) ? $status_change_info['Declined']['portal_name'] : 'N/A',

                'processed_date' => isset($status_change_info['Process']) ? $status_change_info['Process']['created_at']->format('d M Y H:i') : 'N/A',
                'processed_by' => isset($status_change_info['Process']) ? $status_change_info['Process']['created_by_name'] : 'N/A',
                'processed_from' => isset($status_change_info['Process']['portal_name']) ? $status_change_info['Process']['portal_name'] : 'N/A',

                'served_date' => isset($status_change_info['Served']) ? $status_change_info['Served']['created_at']->format('d M Y H:i') : 'N/A',
                'served_by' => isset($status_change_info['Served']) ? $status_change_info['Served']['created_by_name'] : 'N/A',
                'served_from' => isset($status_change_info['Served']['portal_name']) ? $status_change_info['Served']['portal_name'] : 'N/A',

                'cancel_date' => isset($status_change_info['Cancelled']) ? $status_change_info['Cancelled']['created_at']->format('d M Y H:i') : 'N/A',
                'cancel_by' => isset($status_change_info['Cancelled']) ? $status_change_info['Cancelled']['created_by_name'] : 'N/A',
                'cancel_from' => isset($status_change_info['Cancelled']['portal_name']) ? $status_change_info['Cancelled']['portal_name'] : 'N/A'
            ];
        }

        return $data;
    }

    private function getJobs(Request $request)
    {
        $jobs = Job::with('statusChangeLogs', 'resource', 'partnerOrder.partner', 'service.category', 'updateLogs', 'partnerOrder.order');
        $jobs = $this->notLifetimeQuery($jobs, $request->all());
        return $jobs->get();
    }
}