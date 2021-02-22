<?php namespace Sheba\AppVersion;

use Sheba\Dal\AppVersion\AppVersion;
use Sheba\PresentableDTO;

class AppVersionDTO extends PresentableDTO
{
    /** @var AppVersion */
    private $version;
    /** @var bool */
    private $hasCritical;

    /**
     * @param AppVersion $version
     * @return AppVersionDTO
     */
    public function setVersion(AppVersion $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param bool $has_critical
     * @return AppVersionDTO
     */
    public function setHasCritical($has_critical)
    {
        $this->hasCritical = $has_critical;
        return $this;
    }

    public function toArray()
    {
        return [
            'title'       => $this->version ? $this->version->title : null,
            'body'        => $this->version ? $this->version->body : null,
            'height'      => $this->version ? $this->version->height : null,
            'width'       => $this->version ? $this->version->width : null,
            'image_link'  => $this->version ? $this->version->image_link : null,
            'has_update'  => is_null($this->version) ? 0 : 1,
            'is_critical' => $this->hasCritical ? 1 : 0
        ];
    }
}