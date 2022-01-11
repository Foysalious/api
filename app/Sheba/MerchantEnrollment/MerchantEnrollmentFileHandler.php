<?php

namespace Sheba\MerchantEnrollment;

use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

class MerchantEnrollmentFileHandler
{
    use FileManager, CdnFileManager;

    private $uploadFolder;
    private $partner;
    private $uploadedUrl;
    private $partner_basic_information;

    /**
     * @param mixed $partner
     * @return MerchantEnrollmentFileHandler
     */
    public function setPartner($partner): MerchantEnrollmentFileHandler
    {
        $this->partner = $partner;
        $this->partner_basic_information = $partner->basicInformations;
        return $this;
    }

    public function setUploadFolder($upload_folder_key): MerchantEnrollmentFileHandler
    {
        $this->uploadFolder = getNeoBankingFolder(). $this->partner->id . '/';
        return $this;
    }

    public function uploadDocument($file, $form_field): MerchantEnrollmentFileHandler
    {
        if(!empty($this->{$form_field['data_source']}->{$form_field['data_source_id']})) {
            $this->deleteFile($this->{$form_field['data_source']}->{$form_field['data_source_id']});
        }
        list($file, $filename) = $this->makeAttachment($file, $form_field['data_source_id'].$this->partner->id);
        $this->uploadedUrl = $this->saveFileToCDN($file, $form_field['getTradeLicenceDocumentsFolder'](), $filename);
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