<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementListTransformer extends TransformerAbstract
{
    public function transform(Announcement $announcement)
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'target_type' => $announcement->target_type,
            'status' => $this->getStatus($announcement->end_date),
            'end_date' => $announcement->end_date->toDateTimeString(),
            'created_at' => $announcement->created_at->toDateTimeString()
        ];
    }

    private function getStatus($end_date)
    {
        $today_date = Carbon::now();
        if ($end_date->greaterThanOrEqualTo($today_date)) return "Scheduled";
        return "Published";
    }
}