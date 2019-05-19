<?php


namespace Sheba\Business\FormTemplate;


;

use App\Models\FormTemplate;
use App\Models\FormTemplateItem;
use Illuminate\Database\Eloquent\Model;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;

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
        /** @var FormTemplate $form_template */
        $form_template = $this->formTemplateRepository->create($this->formTemplateData);
        $this->makeFormTemplateItemData($form_template);
        $this->formTemplateItemRepository->createMany($this->formTemplateItemData);
        return $form_template;
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

    private function makeFormTemplateItemData(FormTemplate $form_template)
    {
        $this->formTemplateItemData = [];
        $variables = json_decode($this->data['variables']);
        foreach ($variables as $variable) {
            array_push($this->formTemplateItemData, [
                'title' => $variable->title,
                'short_description' => $variable->short_description,
                'long_description' => $variable->instructions,
                'input_type' => $variable->type,
                'form_template_id' => $form_template->id,
                'variables' => json_encode(['is_required' => $variable->is_required]),
            ]);
        }
    }

}