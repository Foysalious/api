<?php namespace Sheba\Attachments;

use Sheba\FileManagers\CdnFileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FilesAttachment
{
    use CdnFileManager;

    /**
     * Store an attachment to cdn.
     *
     * @param  $file
     * @return array
     */
    protected function storeAttachmentToCDN(UploadedFile $file)
    {
        $destinationPath = 'uploads/attachments/';
        $filename = time() . "_" . $file->getClientOriginalName();
        return [
            'file'      => $this->saveFileToCDN($file, $destinationPath, $filename),
            'title'     => $filename,
            'file_type' => $file->getClientOriginalExtension()
        ];
    }
}