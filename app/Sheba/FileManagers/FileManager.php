<?php namespace Sheba\FileManagers;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileManager
{
    protected function uniqueFileName($file, $name)
    {
        if(empty($name)) {
            $name = "TIWNN";
        }
        $name = strtolower(str_replace(' ', '_', $name));
        return time() . "_" . $name . $this->getExtension($file);
    }

    private function getExtension($file)
    {
        return ($file instanceof UploadedFile) ? ("." . $file->getClientOriginalExtension()) : getBase64FileExtension($file);
    }

    protected function makeBanner($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Banner($file))->make();
        return [ $file, $filename ];
    }

    protected function makeAppBanner($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppBanner($file))->make();
        return [ $file, $filename ];
    }

    protected function makeThumb($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Thumb($file))->make();
        return [ $file, $filename ];
    }

    protected function makeAppThumb($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Thumb($file))->make();
        return [ $file, $filename ];
    }

    protected function makeIcon($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Icon($file))->make();
        return [ $file, $filename ];
    }

    protected function makeSlide($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Slide($file))->make();
        return [ $file, $filename ];
    }
}