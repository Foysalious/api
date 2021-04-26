<?php namespace Sheba\FileManagers;


use Intervention\Image\Image;
use Intervention\Image\Facades\Image as ImageFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Image
{
    use CdnFileManager;

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

    public function getNameWithExtension()
    {
        return $this->getName() . "." . $this->getExtension();
    }

    public function getExtension()
    {
        return $this->pathInfo['extension'];
    }

    public function getExtensionFromMime()
    {
        return getExtensionFromMime($this->getImage()->mime());
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
        $this->setImage(ImageFacade::make($file));
        return $this->image;
    }

    /**
     * @param null $dir
     * @return Image
     */
    public function download($dir = null)
    {
        $dir = $dir ?: getTempDownloadFolder();
        $image = $this->getImage();
        $name = $this->getName() . "." . $this->getExtensionFromMime();
        return $image->save($dir . $name);
    }

    /**
     * @param Image $image
     */
    public function replaceImage(Image $image)
    {
        $this->saveImageToCDN($image, $this->getFolder(), $this->getNameWithExtension());
        $this->setImage($image);
    }
}
