<?php

namespace App\Repositories;

use Aws\S3\Exception\S3Exception;
use Storage;
use Aws\S3\S3Client;

class FileRepository
{
    private $s3;

    public function __construct()
    {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_REGION'),
            'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET'),
            ],
        ]);

    }

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
            $s3->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $folder . $filename,
                'Body' => file_get_contents($file),
                'ACL' => 'public-read',
                'ContentType' => $file->getMimeType(),
                'CacheControl' => 'max-age=2628000, public',
            ]);
        } catch (S3Exception $e) {
            return false;
        }
        return env('S3_URL') . $folder . $filename;
    }

    public function uploadImageToCDN($folder, $filename, $image)
    {
        try {
            $this->s3->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $folder . '/' . $filename,
                'Body' => $image,
                'ACL' => 'public-read',
                'ContentType' => $image->mime(),
                'CacheControl' => 'max-age=2628000, public',
            ]);
        } catch (S3Exception $e) {
            return false;
        }
        return env('S3_URL') . $folder . '/' . $filename;
    }
}