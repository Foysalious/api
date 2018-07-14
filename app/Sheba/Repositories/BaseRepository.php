<?php namespace Sheba\Repositories;

use Sheba\FileManagers\FileManager;
use Sheba\FileManagers\CdnFileManager;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class BaseRepository
{
    use FileManager, CdnFileManager, ModificationFields;

    protected $partnerLoggedIn = true;

    protected $requestIdentification;

    public function __construct()
    {
        $this->requestIdentification = new RequestIdentification();
    }

    protected function withRequestIdentificationData($data)
    {
        return $this->requestIdentification->set($data);
    }
}