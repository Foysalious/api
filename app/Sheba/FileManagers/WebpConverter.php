<?php namespace Sheba\FileManagers;

use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;

class WebpConverter extends S3ImageProcessingJob
{
    use CdnFileManager;

    public function handle()
    {
        $image = $this->image->download();
        $source = $image->basePath();
        $destination = getFullNameWithoutExtension($image) . '.webp';

        try {
            WebPConvert::convert($source, $destination);
        } catch (ConversionFailedException $e) {
            logError($e);
        }

        $webp = file_get_contents($destination);
        $this->saveFileStringToCDN($webp, $this->image->getFolder(), getNameWithExtension($destination));

        unlink($source);
        unlink($destination);
    }
}
