<?php namespace Sheba\FileManagers;

use Illuminate\Support\Facades\Storage;
use App\Jobs\FileUploadToS3;

trait CdnFileManager
{
    protected function saveImageToCDN($file, $folder, $filename)
    {
        $s3 = Storage::disk('s3');
        $filename = clean($filename, '_', ['.']);
        $s3->put($folder . $filename, (string)$file, 'public');
        return config('sheba.s3_url') . $folder . $filename;
    }

    protected function saveFileToCDN($file, $folder, $filename)
    {
        $filename = clean($filename, '_', ['.']);
        //dispatch(new FileUploadToS3($folder . $filename, file_get_contents($file), 'public'));
        Storage::disk('s3')->put($folder . $filename, file_get_contents($file), 'public');
        return config('sheba.s3_url') . $folder . $filename;
    }

    protected function deleteImageFromCDN($filename)
    {
        Storage::disk('s3')->delete($filename);
    }
}