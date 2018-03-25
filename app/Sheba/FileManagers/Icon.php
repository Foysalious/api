<?php namespace Sheba\FileManagers;

class Icon extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 144;
        $this->height = 144;
        $this->file = $file;
    }
}