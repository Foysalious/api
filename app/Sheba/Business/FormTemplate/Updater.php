<?php


namespace Sheba\Business\FormTemplate;


use App\Models\FormTemplate;
use Illuminate\Database\QueryException;

class Updater extends Creator
{
    /** @var FormTemplate */
    private $formTemplate;

    public function setFormTemplate(FormTemplate $form_template)
    {
        $this->formTemplate = $form_template;
        return $this;
    }


    public function update()
    {
        try {
            DB::transaction(function () {
                $this->formTemplateRepository->update($this->formTemplate, ['title' => $this->title, 'short_description' => $this->shortDescription]);
                if (isset($this->data['variables'])) {
                    $this->makeItem($this->formTemplate);
                    $this->deleteItems();
                    $this->formTemplateItemRepository->createMany($this->formTemplateItemData);
                }
                if (isset($this->data['questions'])) {
                    $this->makeQuestion($this->formTemplate);
                    $this->deleteQuestions();
                    $this->formTemplateQuestionRepository->createMany($this->formTemplateQuestionData);
                }
            });
        } catch (QueryException $e) {
            throw $e;
        }
    }

    private function deleteItems()
    {
        $this->formTemplate->items()->delete();
    }

    private function deleteQuestions()
    {
        $this->formTemplate->questions()->delete();
    }
}