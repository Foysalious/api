<?php namespace Sheba\Business\Inspection;

class CreateProcessor
{
    private $type;

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getCreationClass()
    {
        if ($this->type == 'once') {
            return app(OneTimeInspectionCreator::class);
        } else {
            return app(ScheduleInspectionCreator::class);
        }
    }
}
