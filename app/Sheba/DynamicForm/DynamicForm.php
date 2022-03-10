<?php

namespace App\Sheba\DynamicForm;

use Sheba\Dal\MefForm\Model as MefForm;
use Sheba\Dal\MefSections\Model as MefSection;

class DynamicForm
{
    private $form;
    private $section;
    private $partner;
    private $requestData;

    public function setForm($form_id): DynamicForm
    {
        $this->form = MefForm::find($form_id);
        return $this;
    }

    public function getFormSections(): array
    {
        $categories = array();
        foreach ($this->form->sections as $section) {
            $categories[] = (new CategoryDetails())->setCategoryCode($section->key)
                ->setCompletionPercentage(100)->setCategoryId($section->id)
                ->setName($section->name, $section->bn_name)->toArray();
        }
        return ["category_list" => $categories];
    }

    public function getSectionDetails(): array
    {
        return [
            "name"   => $this->getSectionNames(),
            "fields" => $this->getSectionFields(),
        ];
    }

    public function postSectionFields()
    {
        dd($this->requestData);
    }

    private function getSectionNames()
    {
        return (new CategoryDetails())->setName($this->section->name, $this->section->bn_name)->getName();
    }

    public function getSectionFields(): array
    {
        $fields = array();
        $form_builder = (new FormFieldBuilder())->setPartner($this->partner);
        foreach ($this->section->fields as $field)
            $fields[] = $form_builder->setField($field)->build()->toArray();

        return $fields;
    }

    /**
     * @param mixed $partner
     * @return DynamicForm
     */
    public function setPartner($partner): DynamicForm
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $section_id
     * @return DynamicForm
     */
    public function setSection($section_id): DynamicForm
    {
        $this->section = MefSection::find($section_id);
        return $this;
    }

    /**
     * @param mixed $requestData
     * @return DynamicForm
     */
    public function setRequestData($requestData): DynamicForm
    {
        $this->requestData = $requestData;
        return $this;
    }
}