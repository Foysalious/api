<?php namespace Sheba\FileManagers;

use Intervention\Image\Image;
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
        if($file instanceof UploadedFile) return "." . $file->getClientOriginalExtension();
        if($file instanceof Image) return "." . explode('/', $file->mime())[1];
        return getBase64FileExtension($file);
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
        $file = (new AppThumb($file))->make();
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

    protected function makeAppSlide($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppSlide($file))->make();
        return [ $file, $filename ];
    }

    protected function makePushNotificationIcon($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PushIcon($file))->make();
        return [ $file, $filename ];
    }

    protected function makePushNotificationImage($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PushImage($file))->make();
        return [ $file, $filename ];
    }
}