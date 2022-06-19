<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementTransformer extends TransformerAbstract
{

    const PUBLISHED = 'published';
    const SCHEDULED = 'scheduled';
    const EXPIRED = 'expired';

    public function transform(Announcement $announcement)
    {
        $target_ids = json_decode($announcement->target_id,1);

        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'target_type' => $announcement->target_type,
            'target_id' => $target_ids,
            'is_published_for_app' => $announcement->is_published,
            #In new Design short description remove. it should change in app
            'short_description' => $announcement->short_description ?: $announcement->long_description,
            'description' => $announcement->long_description ?: $announcement->short_description,
            'status' => $this->getStatus($announcement),
            'end_date' => $this->getEndDate($announcement),
            'created_at' => $announcement->created_at->toDateTimeString()
        ];
    }

    private function getEndDate($announcement)
    {
        #One day addition because In app one day subtract kora
        #For new version it needed
        return ($announcement->end_date->addDay())->toDateString() . ' ' . $announcement->end_time;
    }

    private function getStatus($announcement)
    {
        $today_date = Carbon::now();
        $end_date_format = Carbon::parse($announcement->end_date->toDateString() . ' ' . $announcement->end_time);

        if ($end_date_format->lessThan($today_date)) return "Expired";
        if ($announcement->status == self::PUBLISHED) return "Published";
        if ($announcement->status == self::SCHEDULED) return "Scheduled";
    }
}
