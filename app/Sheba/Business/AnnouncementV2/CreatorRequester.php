<?php namespace App\Sheba\Business\AnnouncementV2;

use App\Models\BusinessMember;
use App\Models\Business;
use Carbon\Carbon;

class CreatorRequester
{
    private $type;
    private $title;
    private $longDescription;
    private $isPublished;
    private $targetType;
    private $targetIds;
    private $schduledFor;
    private $startDate;
    private $startTime;
    private $endDate;
    private $endTime;
    private $status;
    private $announcement;


    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }


    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($long_description)
    {
        $this->longDescription = $long_description;
        return $this;
    }

    public function getDescription()
    {
        return $this->longDescription;
    }

    public function setIsPublished($is_published)
    {
        $this->isPublished = $is_published;
        return $this;
    }

    public function getIsPublished()
    {
        return $this->isPublished;
    }

    public function setTargetType($target_type)
    {
        $this->targetType = $target_type;
        return $this;
    }

    public function getTargetType()
    {
        return $this->targetType;
    }

    public function setTargetIds($target_ids)
    {
        $this->targetIds = $target_ids;
        if (!$this->targetIds) json_encode([]);
        return $this;
    }

    public function getTargetIds()
    {
        return $this->targetIds;
    }

    public function setScheduledFor($scheduled_for)
    {
        $this->schduledFor = $scheduled_for;
        return $this;
    }

    public function getScheduledFor()
    {
        return $this->schduledFor;
    }

    public function setStartDate($start_date)
    {
        $this->startDate = $start_date;
        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartTime($start_time)
    {
        $this->startTime = $start_time;
        return $this;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndDate($end_date)
    {
        $this->endDate = $end_date;
        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndTime($end_time)
    {
        $this->endTime = $end_time;
        return $this;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setAnnouncement($announcement)
    {
        $this->announcement = $announcement;
        return $this;
    }

    public function getAnnouncement()
    {
        return $this->announcement;
    }
}
