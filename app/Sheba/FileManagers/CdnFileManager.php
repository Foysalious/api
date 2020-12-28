<?php namespace Sheba\FileManagers;

use Illuminate\Support\Facades\Storage;

trait CdnFileManager
{
    protected function saveImageToCDN($file, $folder, $filename)
    {
        return $this->putFileToCDNAndGetPath((string)$file, $folder, $filename);
    }

    public function getFullFileUrl($folder, $filename)
    {
        return config('sheba.s3_url') . $this->makeFullFilePath($folder, $filename);
    }

    private function putFileToCDNAndGetPath($file, $folder, $filename, $access_level = "public")
    {
        $filename = $this->makeFullFilePath($folder, $filename);
        $cdn = $this->getCDN();
        if ($access_level == "private") {
            $cdn->put($filename, $file);
        } else {
            $cdn->put($filename, $file, 'public');
        }
        return config('sheba.s3_url') . $filename;
    }

    private function makeFullFilePath($folder, $filename)
    {
        $filename = clean($filename, '_', ['.', '-']);
        return $folder . $filename;
    }

    private function getCDN()
    {
        return Storage::disk('s3');
    }

    protected function saveFileToCDN($file, $folder, $filename)
    {
        return $this->putFileToCDNAndGetPath(file_get_contents($file), $folder, $filename);
    }

    protected function savePrivateFileToCDN($file, $folder, $filename)
    {
        return $this->putFileToCDNAndGetPath(file_get_contents($file), $folder, $filename, 'private');
    }

    protected function deleteImageFromCDN($filename)
    {
        $this->deleteFileFromCDN($filename);
    }

    protected function deleteFileFromCDN($filename)
    {
        $this->getCDN()->delete($filename);
    }
}
