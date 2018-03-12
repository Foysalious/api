<?php namespace Sheba\FileManagers;

use Intervention\Image\Facades\Image;

abstract class ImageManager
{
    /** @var int $width */
    protected $width;
    /** @var int $height */
    protected $height;
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
    protected $file;

    /**
     * @return Image
     */
    public function make()
    {
        $image = Image::make($this->file);
        $image->encode($this->file->getClientOriginalExtension());
        return $image;
    }

    /**
     * @return Image
     */
    public function makeAndResize()
    {
        $image = Image::make($this->file)->resize($this->width, $this->height);
        $image->encode($this->file->getClientOriginalExtension());
        return $image;
    }
}