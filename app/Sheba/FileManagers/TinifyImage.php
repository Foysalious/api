<?php namespace Sheba\FileManagers;


use CURLFile;
use Intervention\Image\Facades\Image as ImageFacade;

class TinifyImage extends S3ImageProcessingJob
{
    public function handle()
    {
        $image = $this->image->download();
        $file = new CURLFile($image->basePath(), $image->mime(), $image->basename);

        $result = $this->call($file);
        $tinified = $this->makeTinifiedImage($result);

        if ($tinified) $this->image->replaceImage($tinified);

        unlink($image->basePath());
    }

    private function call(CURLFile $file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=90');
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "files" => $file
        ]);
        $result = curl_exec($ch);
        if (curl_errno($ch)) $result = curl_error($ch);
        curl_close ($ch);

        return json_decode($result, true);
    }

    private function makeTinifiedImage($result)
    {
        if (!array_key_exists('dest', $result)) return null;
        if ($result['dest_size'] >= $result['src_size']) return null;

        return ImageFacade::make($result['dest'])->encode($this->image->getExtensionFromMime());
    }
}
