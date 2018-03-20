<?php namespace Sheba\FileManagers;

class AppBanner extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 1024;
        $this->height = 768;
        $this->file = $file;
    }
}