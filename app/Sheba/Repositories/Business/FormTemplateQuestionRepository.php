<?php namespace Sheba\Repositories\Business;

use App\Models\FormTemplateQuestion;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\FormTemplateQuestionRepositoryInterface;

class FormTemplateQuestionRepository extends BaseRepository implements FormTemplateQuestionRepositoryInterface
{
    public function __construct(FormTemplateQuestion $form_template_question)
    {
        parent::__construct();
        $this->setModel($form_template_question);
    }
}