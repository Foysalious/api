<?php namespace Sheba\Business\FormTemplateItem;

use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;

class Creator
{
    private $formTemplateItemRepository;
    private $formTemplate;
    private $formTemplateItemData;
    private $data;

    public function __construct(FormTemplateItemRepositoryInterface $form_template_item_repository)
    {
        $this->formTemplateItemRepository = $form_template_item_repository;
        $this->formTemplateItemData = [];
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setFormTemplate($form_template)
    {
        $this->formTemplate = $form_template;
        return $this;
    }

    public function create()
    {
        $this->makeFormTemplateItemData();
        $this->formTemplateItemRepository->createMany($this->formTemplateItemData);
    }

    private function makeFormTemplateItemData()
    {
        $variables = json_decode($this->data['variables']);
        foreach ($variables as $variable) {
            array_push($this->formTemplateItemData, [
                'title' => $variable->title,
                'short_description' => $variable->short_description,
                'long_description' => $variable->instructions,
                'input_type' => $variable->type,
                'form_template_id' => $this->formTemplate->id,
                'variables' => json_encode(['is_required' => (int)$variable->is_required]),
            ]);
        }
    }
}