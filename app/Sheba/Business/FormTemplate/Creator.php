<?php namespace Sheba\Business\FormTemplate;

use App\Models\FormTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use DB;

class Creator
{
    protected $formTemplateRepository;
    protected $formTemplateItemRepository;
    protected $formTemplateData;
    protected $formTemplateItemData;
    protected $data;
    protected $owner;
    /** @var FormTemplateQuestionRepositoryInterface $formTemplateQuestionRepository */
    protected $formTemplateQuestionRepository;
    /** @var array */
    protected $formTemplateQuestionData;

    /**
     * Creator constructor.
     *
     * @param FormTemplateRepositoryInterface $form_template_repository
     * @param FormTemplateItemRepositoryInterface $form_template_item_repository
     * @param FormTemplateQuestionRepositoryInterface $form_template_question_repository
     */
    public function __construct(FormTemplateRepositoryInterface $form_template_repository,
                                FormTemplateItemRepositoryInterface $form_template_item_repository,
                                FormTemplateQuestionRepositoryInterface $form_template_question_repository)
    {
        $this->formTemplateRepository = $form_template_repository;
        $this->formTemplateItemRepository = $form_template_item_repository;
        $this->formTemplateQuestionRepository = $form_template_question_repository;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param Model $owner
     * @return $this
     */
    public function setOwner(Model $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return null
     */
    public function create()
    {
        $this->makeFormTemplateData();
        $form_template = null;
        try {
            DB::transaction(function () use (&$form_template) {
                /** @var FormTemplate $form_template */
                $form_template = $this->formTemplateRepository->create($this->formTemplateData);
                $this->makeItem($form_template);
                $this->formTemplateItemRepository->createMany($this->formTemplateItemData);

                if (isset($this->data['questions'])) {
                    $this->makeQuestion($form_template);
                    $this->formTemplateQuestionRepository->createMany($this->formTemplateQuestionData);
                }
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $form_template;
    }

    /**
     * CREATING FORM TEMPLATE TABLE DATA
     */
    private function makeFormTemplateData()
    {
        $this->formTemplateData = [
            'title' => $this->data['title'],
            'short_description' => $this->data['short_description'],
            'owner_type' => "App\\Models\\" . class_basename($this->owner),
            'owner_id' => $this->owner->id,
            'is_published' => 1,
            'type' => isset($this->data['type']) ? $this->data['type'] : config('b2b.FORM_TEMPLATES.inspection')
        ];
    }

    /**
     * MAKE FORM TEMPLATE ITEM DATA
     *
     * @param FormTemplate $form_template
     */
    protected function makeItem(FormTemplate $form_template)
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

    /**
     * * MAKE FORM TEMPLATE QUESTION DATA
     *
     * @param FormTemplate $form_template
     */
    protected function makeQuestion(FormTemplate $form_template)
    {
        $this->formTemplateQuestionData = [];
        $questions = json_decode($this->data['questions']);
        foreach ($questions as $question) {
            array_push($this->formTemplateQuestionData, [
                'title' => $question->title,
                'short_description' => $question->short_description,
                'long_description' => $question->instructions,
                'input_type' => $question->type,
                'form_template_id' => $form_template->id,
                'variables' => json_encode(['is_required' => $question->is_required]),
            ]);
        }
    }
}