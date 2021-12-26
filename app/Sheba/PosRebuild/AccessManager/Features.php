<?php namespace App\Sheba\PosRebuild\AccessManager;


use Sheba\Helpers\ConstGetter;

class Features
{
    use ConstGetter;

    const PRODUCT_WEBSTORE_PUBLISH = 'product_webstore_publish';
    const INVOICE_DOWNLOAD = 'invoice_download';
}