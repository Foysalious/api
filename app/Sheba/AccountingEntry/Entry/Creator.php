<?php

namespace App\Sheba\AccountingEntry\Entry;


use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Helper\FileUploader;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Exception;

class Creator
{
    use FileUploader;
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

    /**
     * @throws Exception
     */
    public function createEntry()
    {
        $this->entryDto->attachments = $this->entryDto->attachments ?
            $this->uploadAttachments($this->entryDto->attachments) : json_encode([]);
        $data = $this->entryDataMaker->setPartner($this->partner)->setEntryDto($this->entryDto)->makeData();
        return $this->entriesRepo->setPartner($this->partner)->createEntry($data);
    }

}