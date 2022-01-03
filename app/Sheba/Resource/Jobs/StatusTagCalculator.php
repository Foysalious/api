<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use Carbon\Carbon;
use Sheba\Helpers\Converters\NumberLanguageConverter;
use Sheba\Jobs\JobStatuses;

class StatusTagCalculator
{
    private $actionCalculator;

    public function __construct(ActionCalculator $actionCalculator)
    {
        $this->actionCalculator = $actionCalculator;
    }

    public function getOrderStatusMessage(Job $job)
    {
        $now = Carbon::now()->format('H:i');
        if ($job->status == JobStatuses::SERVED && !$job->partnerOrder->isClosedAndPaidAt()) {
            return ['message' => "বিল সংগ্রহ বাকি আছে", 'tag' => 'collection'];
        } elseif ($job->status == JobStatuses::SERVED && $job->partnerOrder->isClosedAndPaidAt()) {
            return ['message' => "কাজ শেষ", 'tag' => 'served'];
        } elseif ($this->isStatusAfterOrEqualToProcess($job->status)) {
            return ['message' => "যে অর্ডার টি এখন চলছে", 'tag' => 'process'];
        } else {
            $job_start_time = $this->getJobStartTime($job);
            $different_in_minutes = Carbon::parse($now)->diffInMinutes($job_start_time);
            $hour = floor($different_in_minutes / 60);
            $minute = $different_in_minutes > 60 ? $different_in_minutes % 60 : $different_in_minutes;
            $hr_message = $hour > 0 ? ($hour . ' ঘণ্টা') : '';
            $min_message = $minute > 0 ? ($minute . ' মিনিট') : '';
            if (!empty($min_message) && !empty($hr_message)) $hr_message .= ' ';
            if (Carbon::parse($now)->lt($job_start_time)) {
                $message = ['message' => "পরের অর্ডার", 'tag' => 'future'];
            } else {
                $message = ['message' => "লেট", 'tag' => 'late'];
            }
            return ['message' => NumberLanguageConverter::en2bn($hr_message . $min_message) . ' ' . $message['message'], 'tag' => $message['tag']];
        }
    }

    public function calculateTag(Job $job)
    {
        $now = Carbon::now();
        $job_start_time = $this->getJobStartTime($job);
        if ($now->gt($job_start_time) && $this->actionCalculator->isStatusBeforeProcess($job->status)) return ['type' => 'late', 'value' => 'Late'];
        if ($job->status == JobStatuses::SERVED && $job->partnerOrder->isClosedAndPaidAt()) return ['type' => 'served', 'value' => 'Served'];
        if ($this->isStatusAfterOrEqualToProcess($job->status)) return ['type' => 'process', 'value' => 'Process'];
        if ($job_start_time->gt($now) && $job_start_time->diffInHours($now) <= 24) return ['type' => 'time', 'value' => Carbon::parse($job->preferred_time_start)->format('H:i A')];
        return ['type' => 'date', 'value' => Carbon::parse($job->schedule_date)->format('M j')];
    }

    /**
     * @param $status
     * @return bool
     */
    private function isStatusAfterOrEqualToProcess($status)
    {
        return constants('JOB_STATUS_SEQUENCE')[$status] >= constants('JOB_STATUS_SEQUENCE')[JobStatuses::PROCESS];
    }

    /**
     * @param Job $job
     * @return Carbon
     */
    private function getJobStartTime(Job $job)
    {
        return Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);
    }
}