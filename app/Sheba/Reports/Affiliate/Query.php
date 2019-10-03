<?php namespace Sheba\Reports\Affiliate;

use App\Models\Affiliate;
use Sheba\Reports\Query as BaseQuery;
use Sheba\Helpers\TimeFrame;

class Query extends BaseQuery
{
    /** @var TimeFrame */
    private $timeFrame;

    private static $columns = [
    ];

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function build()
    {
        return $this->normalQuery();
    }

    private function normalQuery()
    {
        return Affiliate::withCount('successful_lead')
            ->with(['profile', 'location', 'ambassador.profile', 'affiliations' => function ($query) {
                $query->whereBetween('created_at', $this->timeFrame->getArray());
            }, 'transactions' => function ($query) {
                $query->whereBetween('created_at', $this->timeFrame->getArray());
            }, 'partnerAffiliations' => function ($query) {
                $query->whereBetween('created_at', $this->timeFrame->getArray());
            }])->whereBetween('created_at', $this->timeFrame->getAssociativeArray());
    }

    private function optimizedQuery() {}
}
