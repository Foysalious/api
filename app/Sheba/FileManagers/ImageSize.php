<?php namespace Sheba\FileManagers;


class ImageSize
{
    /** @var int */
    private $height;
    /** @var int */
    private $width;

    public function __construct($height, $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function toString()
    {
        return $this->width . "x" . $this->height;
    }
}