<?php namespace Sheba\Business\Procurement;

use App\Sheba\Attachments\Attachments;
use App\Models\Attachment;

class AttachmentUpdater
{
    private $attachmentManager;
    private $attachmentsForAdd;
    private $attachmentsForDelete;
    private $createdBy;
    private $procurement;

    public function __construct(Attachments $attachment_manager)
    {
        $this->attachmentManager = $attachment_manager;
    }

    public function setProcurement($procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setAttachmentsForAdd($documents_for_add)
    {
        $this->attachmentsForAdd = $documents_for_add;
        return $this;
    }

    public function setAttachmentsForDelete($documents_for_delete)
    {
        $this->attachmentsForDelete = $documents_for_delete;
        return $this;
    }

    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
        return $this;
    }

    public function addAttachments()
    {
        foreach ($this->attachmentsForAdd as $attachment) {
            $this->attachmentManager->setAttachableModel($this->procurement)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
        }
    }

    public function deleteAttachments()
    {
        foreach ($this->attachmentsForDelete as $attachment) {
            $attachment_to_be_deleted = Attachment::find($attachment->id);
            $attachment_to_be_deleted->delete();
        }
    }
}