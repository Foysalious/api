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
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'is_published_for_app' => $announcement->is_published,
            'short_description' => $announcement->short_description,
            'description' => $announcement->long_description ?: $announcement->short_description,
            'status' => $this->getStatus($announcement),
            'end_date' => $this->getEndDate($announcement),
            'created_at' => $announcement->created_at->toDateTimeString()
        ];
    }

    private function getEndDate($announcement)
    {
        return $announcement->end_date->toDateString() . ' ' . $announcement->end_time;
    }

    private function getStatus($announcement)
    {
        $today_date = Carbon::now()->toDateString();
        $end_date = $announcement->end_date->toDateString();

        if (($end_date < $today_date)) return "Expired";
        if ($announcement->status == self::PUBLISHED) return "Published";
        if ($announcement->status == self::SCHEDULED) return "Scheduled";
    }
}
