<?php namespace Sheba\ExpenseTracker\Repository;

use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\Helpers\TimeFrame;

class StatsRepository extends BaseRepository
{
    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function saveAll()
    {
        $this->client->post('daily-stats/save-all-of-yesterday', []);
        return [];
    }

    /**
     * @param TimeFrame $time_frame
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function between(TimeFrame $time_frame)
    {
        $start = $time_frame->start->toDateString();
        $end = $time_frame->end->toDateString();
        return $this->client->get("accounts/$this->accountId/stats?start_date=$start&end_date=$end");
    }
}
