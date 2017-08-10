<?php

namespace App\Repositories;

use Aws\S3\Exception\S3Exception;
use Storage;
use Aws\S3\S3Client;

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
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_REGION'),
            'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET'),
            ],
        ]);
        try {
            $result = $s3->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $folder . $filename,
                'Body' => file_get_contents($file),
                'ACL' => 'public-read',
                'CacheControl' => 'max-age=2628000, public',
            ]);
        } catch (S3Exception $e) {
            return false;
        }
        return $result['ObjectURL'];
//        Storage::disk('s3')->put($folder . $filename, file_get_contents($file), ['visibility' => 'public', 'CacheControl' => 'max-age=2628000, public']);
//        return env('S3_URL') . $folder . $filename;
    }
}