<?php


namespace Sheba\Business\FormTemplate;


use App\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use App\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class Creator
{
    private $formTemplateRepository;
    private $formTemplateItemRepository;
    private $formTemplateData;
    private $formTemplateItemData;
    private $data;
    private $owner;

    public function __construct(FormTemplateRepositoryInterface $form_template_repository, FormTemplateItemRepositoryInterface $form_template_item_repository)
    {
        $this->formTemplateRepository = $form_template_repository;
        $this->formTemplateItemRepository = $form_template_item_repository;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setOwner(Model $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function create()
    {
        $this->makeFormTemplateData();
        $this->makeFormTemplateItemData();
    }

    private function makeFormTemplateData()
    {
        $this->formTemplateData = [
            'title' => $this->data['title'],
            'short_description' => $this->data['short_description'],
            'owner_type' => "App\\Models\\" . class_basename($this->owner),
            'owner_id' => $this->owner->id,
            'is_published' => 1
        ];
    }

    private function makeFormTemplateItemData()
    {
        $this->formTemplateItemData = [
            'title' => $this->data['title'],
            'short_description' => $this->data['short_description'],
            'owner_type' => "App\\Models\\" . class_basename($this->owner),
            'owner_id' => $this->owner->id,
            'is_published' => 1
        ];
    }

}