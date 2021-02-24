<?php namespace App\Sheba\FileManagers;


use Sheba\FileManagers\ImageManager;

class PartnerChequeBookReceipt extends ImageManager
{
    public function __construct($file)
    {
        $this->file = $file;
    }
}