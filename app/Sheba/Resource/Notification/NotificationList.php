<?php namespace Sheba\Resource\Notification;


use App\Http\Requests\Request;
use App\Models\Resource;
use Carbon\Carbon;

class NotificationList
{
    private $resource;

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function getTodaysNotifications()
    {
        $todays_notifications = $this->resource->notifications()->where('created_at', '>=', Carbon::today())->orderBy('id', 'desc')->get();
        $today_final = [];
        $todays_notifications->each(function ($notification) use (&$today_final) {
            array_push($today_final, [
                'id' => $notification->id,
                'message' => $notification->title,
                'description' => $notification->description,
                'type' => $notification->getType(),
                'type_id' => $notification->event_id,
                'is_seen' => $notification->is_seen,
                'created_at' => $notification->created_at->toDateTimeString()
            ]);
        });
        return $today_final;
    }

    public function getThisWeeksNotifications()
    {
        $week_start = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $this_week_notifications = $this->resource->notifications()->where('created_at', '<', Carbon::today())->where('created_at', '>=', $week_start)->orderBy('id', 'desc')->get();
        $this_week_final = [];
        $this_week_notifications->each(function ($notification) use (&$this_week_final) {
            array_push($this_week_final, [
                'id' => $notification->id,
                'message' => $notification->title,
                'description' => $notification->description,
                'type' => $notification->getType(),
                'type_id' => $notification->event_id,
                'is_seen' => $notification->is_seen,
                'created_at' => $notification->created_at->toDateTimeString()
            ]);
        });
        return $this_week_notifications;
    }

    public function getThisMonthsNotifications()
    {
        $week_start = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $firstDay = Carbon::now()->firstOfMonth();
        $this_month_notifications = $this->resource->notifications()->where('created_at', '>=', $firstDay)->where('created_at', '<', $week_start)->orderBy('id', 'desc')->get();
        $this_month_final = [];
        $this_month_notifications->each(function ($notification) use (&$this_month_final) {
            array_push($this_month_final, [
                'id' => $notification->id,
                'message' => $notification->title,
                'description' => $notification->description,
                'type' => $notification->getType(),
                'type_id' => $notification->event_id,
                'is_seen' => $notification->is_seen,
                'created_at' => $notification->created_at->toDateTimeString()
            ]);
        });
        return $this_month_final;
    }

    public function getEarlierNotifications()
    {
        $firstDay = Carbon::now()->firstOfMonth();
        list($offset, $limit) = calculatePagination(\request());
        $earlier_notifications = $this->resource->notifications()->where('created_at', '<', $firstDay)->orderBy('id', 'desc')->skip($offset)->take($limit)->get();
        $earlier_final = [];
        $earlier_notifications->each(function ($notification) use (&$earlier_final) {
            array_push($earlier_final, [
                'id' => $notification->id,
                'message' => $notification->title,
                'description' => $notification->description,
                'type' => $notification->getType(),
                'type_id' => $notification->event_id,
                'is_seen' => $notification->is_seen,
                'created_at' => $notification->created_at->toDateTimeString()
            ]);
        });
        return $earlier_final;
    }
}