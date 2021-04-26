<?php namespace Sheba\FileManagers;


class ImageResizer
{
    use CdnFileManager;

    /** @var S3Image */
    private $image;
    /** @var ImageSize[] */
    private $sizes = [];

    private $webpUrls;
    private $originalExtUrls;

    private $webpImages;
    private $originalExtImages;

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
            $webp[$size->toString()] = $this->buildResizedFileNameWithUrl($size, "webp");
            $original[$size->toString()] = $this->buildResizedFileNameWithUrl($size, $this->image->getExtension());
        }

        $this->webpUrls = $webp;
        $this->originalExtUrls = $original;
    }

    private function buildResizedFileNameWithUrl(ImageSize $size, $ext)
    {
        return $this->buildResizedFileName($size, true, $ext);
    }

    private function buildResizedFileNameWithOutUrl(ImageSize $size, $ext)
    {
        return $this->buildResizedFileName($size, false, $ext);
    }

    private function buildResizedFileName(ImageSize $size, $with_url, $ext)
    {
        $name = $with_url ? $this->image->getFullNameWithoutExtension() : $this->image->getName();
        return $name . "_" . $size->toString() . "." . $ext;
    }

    public function getWebpUrls()
    {
        return $this->webpUrls;
    }

    public function getOriginalExtUrls()
    {
        return $this->originalExtUrls;
    }

    public function getWebpImages()
    {
        return $this->webpImages;
    }

    public function getOriginalExtImages()
    {
        return $this->originalExtImages;
    }

    public function resize()
    {
        $original_image = $this->image->getImage();
        $ext = $this->image->getExtension();

        $webp = [];
        $original = [];

        foreach ($this->sizes as $size) {
            $resized_image = $original_image->resize($size->getWidth(), $size->getHeight());

            /**
             * Webp format is not supported by PHP installation.
             * For now, it is encoded in original extension.
             * It is converted later/below in background.
             */
            $webp_image = $resized_image->encode($ext);
            $org_ext_resized_image = $resized_image->encode($ext);

            $webp[$this->buildResizedFileNameWithOutUrl($size, "webp")] = $webp_image;
            $original[$this->buildResizedFileNameWithOutUrl($size, $ext)] = $org_ext_resized_image;
        }

        $this->webpImages = $webp;
        $this->originalExtImages = $original;
    }

    public function resizeAndSave()
    {
        $this->resize();
        $folder = $this->image->getFolder();

        foreach ($this->getWebpImages() as $webp_filename => $webp_image) {
            $url = $this->saveImageToCDN($webp_image, $folder, $webp_filename);
            $image = new S3Image($url);
            dispatch(new TinifyImage($image));
            dispatch(new WebpConverter($image));
        }
        foreach ($this->getOriginalExtImages() as $org_ext_filename => $org_ext_image) {
            $url = $this->saveImageToCDN($org_ext_image, $folder, $org_ext_filename);
            dispatch(new TinifyImage(new S3Image($url)));
        }
    }
}