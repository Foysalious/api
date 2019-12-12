<?php namespace App\Transformers\Business;


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
            'description' => $announcement->short_description,
            'end_date' => $announcement->end_date->toDateTimeString(),
            'created_at' => $announcement->created_at->toDateTimeString(),
//            'date' => $announcement->end_date->format('M d'),
//            'time' => $announcement->end_date->format('h:i A')
        ];
    }
}