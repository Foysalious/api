<?php namespace Sheba\FileManagers;

class PushImage extends ImageManager
{
    public function __construct($file)
    {
        $this->width    = 160;
        $this->height   = 160;
        $this->file     = $file;
    }
}