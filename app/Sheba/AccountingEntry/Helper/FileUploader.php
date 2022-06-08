<?php

namespace App\Sheba\AccountingEntry\Helper;

use Illuminate\Http\UploadedFile;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

trait FileUploader
{
    use CdnFileManager, FileManager;

    public function uploadAttachments($data)
    {
        $attachments = $this->uploadFiles($data);
        return json_encode($attachments);
    }


    private function uploadFiles($files): array
    {
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file) && $file instanceof UploadedFile) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return $attachments;
    }
}