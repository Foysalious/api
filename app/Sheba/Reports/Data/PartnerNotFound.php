<?php namespace Sheba\Reports\Data;

use App\Models\Event;
use Illuminate\Http\Request;
use Sheba\Events\EventRepository;
use Sheba\Reports\ReportData;

class PartnerNotFound extends ReportData
{
    /** @var  EventRepository */
    private $repo;

    public function __construct(EventRepository $repo)
    {
        $this->repo = $repo;
    }

    public function get(Request $request)
    {
        $events = Event::partnerNotFound();
        $events = $this->notLifetimeQuery($events, $request, 'events.created_at');
        return $events->get()->map(function ($event) {
            return $this->repo->map($event);
        });
    }

}