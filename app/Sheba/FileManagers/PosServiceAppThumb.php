<?php namespace Sheba\FileManagers;

class PosServiceAppThumb extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 300;
        $this->height = 300;
        $this->file = $file;
    }
}