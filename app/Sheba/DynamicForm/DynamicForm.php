<?php

namespace App\Sheba\DynamicForm;

use Sheba\Dal\MefForm\Model as MefForm;

class DynamicForm
{
    private $form_id;
    private $form;

    /**
     * @param mixed $form_id
     * @return DynamicForm
     */
    public function setFormId($form_id): DynamicForm
    {
        $this->form_id = $form_id;
        $form = MefForm::find($form_id);
        $this->setForm($form);
        return $this;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    public function getFormCategory(): array
    {
        $categories = array();
        foreach ($this->form->sections as $section) {
            $categories[] = (new CategoryDetails())->setCategoryCode($section->key)
                ->setCompletionPercentage(100)->setCategoryId($section->id)
                ->setName($section->name, $section->bn_name)->toArray();
        }
        return ["category_list" => $categories];
    }
}