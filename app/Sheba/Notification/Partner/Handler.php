<?php namespace Sheba\Notification\Partner;

use Illuminate\Database\Eloquent\Model;

abstract class Handler
{
    /**
     * Model @var
     */
    protected $model;
    protected $title;
    protected $eventType;
    protected $eventId;
    protected $description;
    protected $link;

    /**
     * @param mixed $title
     * @return Handler
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $eventType
     * @return Handler
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
        return $this;
    }

    /**
     * @param mixed $eventId
     * @return Handler
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * @param mixed $description
     * @return Handler
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $link
     * @return Handler
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }
    abstract function getList($offset,$limit);
    abstract function getDetails($notification);
}
