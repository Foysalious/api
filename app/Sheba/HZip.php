<?php

namespace Sheba;

use ZipArchive;

class HZip
{
    /**
     * Zip a folder (include itself).
     * Usage:
     *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];
        $z = new ZipArchive();
        $z->open($outZipPath, ZIPARCHIVE::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
    }

    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    public static function downloadFiles($files, $downloadDir)
    {
        $downloaded_files = [];
        if (is_dir($downloadDir)) {
            system('rm -rf ' . escapeshellarg($downloadDir), $retval);
        }
        mkdir($downloadDir, 0777);
        foreach ($files as $key => $value) {
            if (!empty($value) && is_string($value)) {
                try {
                    $f = self::downLoadFile($value, $downloadDir);
                    if ($f) $downloaded_files[] = $f;
                } catch (\Throwable $e) {
                }
            }
        }
        return $downloaded_files;
    }

    public static function downLoadFile($url, $downloadDir)
    {
        $file = file_get_contents($url);
        if ($file) {
            $f = file_put_contents($downloadDir . '/' . basename($url), $file);
            return $f;
        } else {
            return null;
        }
    }
}
