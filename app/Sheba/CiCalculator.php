<?php namespace Sheba;

use App\Models\Job;
use Carbon\Carbon;

class CiCalculator
{
    private $job;
    private $jobStatuses;
    private $jobPartnerChangeLog;
    private $jobCancelLog;
    private $jobReview;

    public function __construct(Job $job)
    {
        $this->job = $job;
        $this->jobPartnerChangeLog = $job->partnerChangeLog;
        $this->jobCancelLog = $job->cancelLog;
        $this->jobStatuses = constants('JOB_STATUSES');
        $this->jobReview = $job->review;
    }

    public function calculate()
    {
        return $this->lifetimeCi() + $this->complainCi() + $this->ratingCi() + $this->partnerReAssignCi();
    }

    /**
     * @return double
     */
    private function lifetimeCi()
    {
        return ($this->job->department() == "FM") ? $this->_lifetimeCiForFM() : $this->_lifetimeCiForSMOrSD();
    }

    /**
     * @return double
     */
    private function _lifetimeCiForFM()
    {
        $job_lifetime = $this->job->lifetime();
        if ($job_lifetime < 8 ) {
            return 0.00;
        } elseif ($job_lifetime > 7 && $job_lifetime < 15) {
            return 0.05;
        } elseif ($job_lifetime > 14 && $job_lifetime < 29) {
            return 0.08;
        } else {
            return 0.25;
        }
    }

    /**
     * @return double
     */
    private function _lifetimeCiForSMOrSD()
    {
        $job_lifetime = $this->job->lifetime();
        if ($job_lifetime == 0) {
            return 0;
        } elseif ($job_lifetime > 0 && $job_lifetime < 4) {
            return 0.03;
        } elseif ($job_lifetime > 3 && $job_lifetime < 8) {
            return 0.06;
        } else {
            return 0.25;
        }
    }

    private function complainCi()
    {
        return $this->job->complains->count() ? 0.25 : 0;
    }

    private function ratingCi()
    {
        if (!$this->jobReview || $this->jobReview->rating == 5) {return 0; }
        elseif ($this->jobReview->rating == 4) { return 0.03; }
        elseif ($this->jobReview->rating == 3) { return 0.08; }
        elseif ($this->jobReview->rating == 2) { return 0.10; }
        else { return 0.25; }

        #return (!$this->jobReview || $this->jobReview->rating == 1) ? 0 : ((5 - $this->jobReview->rating)/5);
    }

    private function partnerReAssignCi()
    {
        if (!$this->jobPartnerChangeLog) {
            return 0;
        } elseif ($this->jobPartnerChangeLog->from_status == $this->jobStatuses['Pending']) {
            return 0.05;
        } elseif ($this->jobPartnerChangeLog->from_status == $this->jobStatuses['Accepted']) {
            return 0.10;
        } elseif ($this->jobPartnerChangeLog->from_status == $this->jobStatuses['Process']) {
            return 0.25;
        }
        #return !$this->jobPartnerChangeLog ? 0 : (($this->jobPartnerChangeLog->from_status == $this->jobStatuses['Process']) ? 1 : 0.5);
    }
}