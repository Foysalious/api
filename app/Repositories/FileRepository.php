<?php

namespace App\Repositories;

use Carbon\Carbon;
use Storage;

class FileRepository
{
    public function deleteFileFromCDN($filename)
    {
        if ($filename != '') {
            Storage::disk('s3')->delete($filename);
        }
    }

    public function uploadImage($profile, $photo, $folder, $extension = ".jpg")
    {
        $filename = $profile->id . '_profile_image_'.Carbon::now()->timestamp . $extension;
        $s3 = Storage::disk('s3');
        $s3->put($folder . $filename, file_get_contents($photo), 'public');
        return env('S3_URL') . $folder . $filename;
    }
}