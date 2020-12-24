<?php namespace Sheba\FileManagers;


class S3Image
{
    private $url;
    private $pathInfo;

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
}