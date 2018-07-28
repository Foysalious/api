<?php namespace Sheba\FileManagers;

use Intervention\Image\Facades\Image;

class AppSlide extends ImageManager
{
    public function __construct($file)
    {
        $this->width = 1920;
        $this->height = 734;
        $this->file = $file;
    }

    /**
     * @return Image
     */
    public function make()
    {
        $image = Image::make($this->file)->resize($this->width, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image->encode($this->file->getClientOriginalExtension());
        return $image;
    }
}