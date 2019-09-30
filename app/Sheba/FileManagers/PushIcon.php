<?php namespace Sheba\FileManagers;

class PushIcon extends ImageManager
{
    public function __construct($file)
    {
        $this->width    = 48;
        $this->height   = 48;
        $this->file     = $file;
    }
}