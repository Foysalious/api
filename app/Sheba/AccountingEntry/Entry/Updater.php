<?php

namespace App\Sheba\AccountingEntry\Entry;


use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Helper\FileUploader;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use App\Sheba\AccountingEntry\Service\DueTrackerContactResolver;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class Updater
{
    protected $entriesRepo;
    /* @var EntryDTO $entryDto */
    protected $entryDto;
    protected $partner;
    protected $entryDataMaker;

    public function __construct(EntriesRepository $entries_repo, EntryDataMaker $entry_data_maker)
    {
        $this->entriesRepo = $entries_repo;
        $this->entryDataMaker = $entry_data_maker;
    }

    /**
     * @param mixed $entryDto
     */
    public function setEntryDto(EntryDTO $entryDto)
    {
        $this->entryDto = $entryDto;
        return $this;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function updateEntry()
    {
        $data = $this->entryDataMaker->setPartner($this->partner)->makeData($this->entryDto);
        return $this->entriesRepo->setPartner($this->partner)->updateEntry($data, $this->entryDto->entry_id);
    }

}