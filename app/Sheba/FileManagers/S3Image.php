<?php namespace Sheba\FileManagers;


use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Image
{
    private $url;
    private $pathInfo;
    /** @var Image */
    private $image;
    /** @var UploadedFile */
    private $file;

    public function __construct($url)
    {
        $this->url = $url;
        $this->pathInfo = pathinfo($this->url);
    }

    public function getFullNameWithoutExtension()
    {
        return $this->pathInfo['dirname'] . '/' . $this->getName();
    }

    public function getName()
    {
        return $this->pathInfo['filename'];
    }

    public function getExtension()
    {
        return $this->pathInfo['extension'];
    }

    public function getFolder()
    {
        return substr($this->pathInfo['dirname'], strlen(config('s3.url')));
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
        return $this;
    }

    public function setImage(Image $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        if ($this->image) return $this->image;

        $file = $this->file ?: $this->url;
        $this->setImage(Image::make($file));
        return $this->image;
    }
}