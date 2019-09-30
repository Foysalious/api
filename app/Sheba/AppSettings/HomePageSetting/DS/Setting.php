<?php namespace Sheba\AppSettings\HomePageSetting\DS;

use Carbon\Carbon;

class Setting
{
    /** @var array */
    protected $sections = [];
    /** @var  Carbon */
    protected $updatedAt;

    /**
     * @param Section $section
     * @return $this
     */
    public function push(Section $section)
    {
        $this->sections[] = $section;
        return $this;
    }

    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @return Carbon
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function get()
    {
        return [
            'sections' => $this->convertSectionsToArray(),
            'updated_at' => $this->updatedAt ? $this->updatedAt->toDateTimeString() : null,
            'updated_at_timestamp' => $this->updatedAt ? $this->updatedAt->timestamp : null,
        ];
    }

    public function toJson()
    {
        return json_encode($this->get());
    }

    private function convertSectionsToArray()
    {
        $sections = [];
        foreach ($this->sections as $section) {
            /** @var Section $section */
            $sections[] = $section->toArray();
        }
        return $sections;
    }
}