<?php namespace Sheba\Repositories;

use Sheba\FileManagers\FileManager;
use Sheba\FileManagers\CdnFileManager;
use Sheba\ModificationFields;

class BaseRepository
{
    use FileManager, CdnFileManager, ModificationFields;
}