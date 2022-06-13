<?php

namespace App\Sheba\AccountingEntry\Entry;


use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Helper\FileUploader;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Exception;

class Updater
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
    public function updateEntry()
    {
        $this->entryDto->attachments = $this->updateAttachments();
        $data = $this->entryDataMaker->setPartner($this->partner)->setEntryDto($this->entryDto)
            ->makeData();
        $response = $this->entriesRepo->setPartner($this->partner)->updateEntry($data, $this->entryDto->entry_id);
        if($this->entryDto->delete_attachments) {
            $this->deleteAttachments();
        }
        return $response;
    }

    private function updateAttachments(): string
    {
        $attachments = [];
        if($this->entryDto->old_attachments) {
            $attachments = $this->entryDto->old_attachments;
        }
        if(!is_null($this->entryDto->attachments)) {
            $new_attachments = $this->uploadFiles($this->entryDto->attachments);
            $attachments= array_merge($attachments,$new_attachments);
        }
        return json_encode($attachments);
    }

    private function deleteAttachments()
    {
        foreach ($this->entryDto->delete_attachments as $item){
            $this->deleteFile($item);
        }
    }

}