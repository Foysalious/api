<?php namespace Sheba\Repositories\Business;

use App\Models\FormTemplate;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;

class FormTemplateRepository extends BaseRepository implements FormTemplateRepositoryInterface
{
    public function __construct(FormTemplate $formTemplate)
    {
        parent::__construct();
        $this->setModel($formTemplate);
    }
}