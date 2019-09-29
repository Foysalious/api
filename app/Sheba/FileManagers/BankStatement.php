<?php namespace Sheba\FileManagers;

class BankStatement extends ImageManager
{
    public function __construct($file)
    {
        $this->file = $file;
    }
}