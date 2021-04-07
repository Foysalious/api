<?php


namespace Sheba\NeoBanking;


use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

class NeoBankingFileHandler
{
    use FileManager, CdnFileManager;

    private $uploadFolder;
    private $partner;
    private $uploadedUrl;
    private function setUploadFolder()
    {
        $this->uploadFolder   = getNeoBankingFolder(). $this->partner->id . '/';
        return $this;
    }

    public function getImageUrl($file, $key) {
        $this->setUploadFolder();
        list($file, $filename) = $this->makeNeoBankingFile($file, $key);
        return $this->saveFileToCDN($file, $this->uploadFolder, $filename);
    }

    /**
     * @param mixed $partner
     * @return NeoBankingFileHandler
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }
    public function uploadDocument($file, $key)
    {
        $this->setUploadFolder();
        list($file, $filename) = $this->makeNeoBankingFile($file, $key);
        $this->uploadedUrl = $this->saveFileToCDN($file, $this->uploadFolder, $filename);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadedUrl()
    {
        return $this->uploadedUrl;
    }

}
