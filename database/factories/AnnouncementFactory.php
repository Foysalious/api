<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementFactory extends Factory
{
    protected $dal = Announcement::class;

    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'business_id' => 1,
            'title' => 'Holiday notice',
            'short_description' => 'As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption',
            'long_description' => 'As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption',
            'type' => 'holiday',
            'is_published' => 1,
        ]);
    }
}