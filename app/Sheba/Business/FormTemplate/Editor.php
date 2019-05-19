<?php namespace Sheba\Business\FormTemplate;


use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;

class Editor
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

}