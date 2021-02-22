<?php namespace Sheba\FileManagers;

use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

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
        if($this->file instanceof Image) return $this->file->encode($this->file->extension);

        $image = ImageFacade::make($this->file);
        $image->encode($this->file->getClientOriginalExtension());
        return $image;
    }

    /**
     * @return Image
     */
    public function makeAndResize()
    {
        $image = ImageFacade::make($this->file)->resize($this->width, $this->height);
        $image->encode($this->file->getClientOriginalExtension());
        return $image;
    }
}
