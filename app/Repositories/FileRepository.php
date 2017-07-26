<?php

namespace App\Repositories;

use Storage;

class FileRepository
{
    public function deleteFileFromCDN($filename)
    {
        if ($filename != '') {
            Storage::disk('s3')->delete($filename);
        }
    }

    public function uploadToCDN($filename, $file, $folder)
    {
        Storage::disk('s3')->put($folder . $filename, file_get_contents($file), 'public');
        return env('S3_URL') . $folder . $filename;
    }
}