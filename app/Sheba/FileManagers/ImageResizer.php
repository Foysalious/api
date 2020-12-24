<?php namespace Sheba\FileManagers;


class ImageResizer
{
    /** @var S3Image */
    private $image;
    /** @var ImageSize[] */
    private $sizes = [];

    private $webpUrls;
    private $originalExtUrls;

    public function setImage(S3Image $image)
    {
        $this->image = $image;
        return $this;
    }

    public function pushSize(ImageSize $size)
    {
        array_push($this->sizes, $size);
        return $this;
    }

    public function buildUrls()
    {
        $webp = [];
        $original = [];
        foreach ($this->sizes as $size) {
            $name = $this->image->getFullNameWithoutExtension();
            $ext = $this->image->getExtension();
            $webp[$size->toString()] = $name . "_" . $size->toString() . ".webp";
            $original[$size->toString()] = $name . "_" . $size->toString() . "." . $ext;
        }

        $this->webpUrls = $webp;
        $this->originalExtUrls = $original;
    }

    public function getWebpUrls()
    {
        return $this->webpUrls;
    }

    public function getOriginalExtUrls()
    {
        return $this->originalExtUrls;
    }

    public function resize()
    {

    }
}