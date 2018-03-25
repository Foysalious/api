<?php namespace Sheba\FileManagers;

class Banner extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 1024;
        $this->height = 768;
        $this->file = $file;
    }
}