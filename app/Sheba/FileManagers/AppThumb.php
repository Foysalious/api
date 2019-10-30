<?php namespace Sheba\FileManagers;

class AppThumb extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 300;
        $this->height = 300;
        $this->file = $file;
    }
}