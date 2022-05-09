<?php

namespace App\Sheba\AccountingEntry\Helper;

use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

trait FileUploader
{
    use CdnFileManager, FileManager;

    public function uploadAttachments($request)
    {
        $attachments = $this->uploadFiles($request);
        return json_encode($attachments);
    }


    private function uploadFiles($request): array
    {
        $attachments = [];

        if (isset($request->attachments) && !empty($request->attachments) && request()->hasFile('attachments')) {
            foreach (request()->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return $attachments;
    }
}