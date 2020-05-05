<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementTransformer extends TransformerAbstract
{
    public function transform(Announcement $announcement)
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => $announcement->type,
            'short_description' => $announcement->short_description,
            'description' => $announcement->long_description,
            'status' => $this->getStatus($announcement->end_date),
            'end_date' => $announcement->end_date->toDateTimeString(),
            'created_at' => $announcement->created_at->toDateTimeString()
        ];
    }

    private function getStatus($end_date)
    {
        $today_date = Carbon::now();
        if ($end_date->greaterThanOrEqualTo($today_date)) return "Ongoing";
        return "Previous";
    }
}
