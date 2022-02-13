<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementShowTransformer extends TransformerAbstract
{
    public function transform(Announcement $announcement)
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'description' => $announcement->long_description,
            'type' => $announcement->type,
            'target' => [
                'type' => $announcement->target_type,
                'count' => 8,
            ],
            'active_between' => 'Dec 02,2020-Dec 05,2020',
            'status' => $this->getStatus($announcement->end_date),
            'end_date' => $announcement->end_date->toDateTimeString(),
            'created_by' => $announcement->created_by ?: 'N/S',
            'created_at' => $announcement->created_at->toDateTimeString(),
            'updated_by' => $announcement->updated_by ?: 'N/S',
            'updated_at' => $announcement->updated_at->toDateTimeString(),
        ];
    }

    private function getStatus($end_date)
    {
        $today_date = Carbon::now();
        if ($end_date->greaterThanOrEqualTo($today_date)) return "Scheduled";
        return "Published";
    }
}