<?php namespace App\Sheba\Attachments;

use Illuminate\Database\QueryException;
use Sheba\Attachments\FilesAttachment;
use App\Models\Attachment;
use DB;

class Attachments
{
    use FilesAttachment;

    private $attachableType;
    private $attachableModel;
    private $attachableId;
    private $model_name = 'App\Models\\';
    private $file;

    public function hasError($data)
    {
        if ($data->hasFile('file')) {
            return false;
        }
        return true;
    }

    public function setAttachableType($attachable_type)
    {
        $this->attachableType = $this->model_name . ucfirst(camel_case($attachable_type));
        return $this;
    }

    public function setAttachableId($attachable_id)
    {
        $this->attachableId = (int)$attachable_id;
        return $this;
    }

    public function getAttachableId()
    {
        return $this->attachableId;
    }

    public function getAttachableModel()
    {
        $this->attachableModel = app($this->attachableType)::findOrFail($this->attachableId);
        return $this->attachableModel;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function store()
    {
        $model = $this->getAttachableModel();
        $data = $this->storeAttachmentToCDN($this->file);
        $attachment = new Attachment();
        try {
            DB::transaction(function () use ($attachment, $data) {
                $attachment->attachable_type = $this->attachableType;
                $attachment->attachable_id = $this->attachableId;
                $attachment->title = $data['title'];
                $attachment->file = $data['file'];
                $attachment->file_type = $data['file_type'];
                $attachment->created_by = $this->getAdmin()->id;
                $attachment->created_by_name = $this->getName();
                $attachment->save();
            });
        } catch (QueryException $e) {
            return false;
        }
        return $attachment;
    }

    private function getName()
    {
        try {
            if ($this->attachableModel->profile) {
                return $this->attachableModel->profile->name;
            } else {
                return $this->attachableModel->getContactPerson();
            }

        } catch (QueryException $e) {
            return false;
        }
    }

    private function getAdmin()
    {
        try {
            return $this->attachableModel->getAdmin() ? $this->attachableModel->getAdmin() : $this->attachableModel;
        } catch (QueryException $e) {
            return false;
        }
    }

}