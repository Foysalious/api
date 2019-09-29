<?php namespace Sheba\Repositories\Business;


use App\Models\FormTemplateItem;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;

class FormTemplateItemRepository extends BaseRepository implements FormTemplateItemRepositoryInterface
{

    public function __construct(FormTemplateItem $formTemplateItem)
    {
        parent::__construct();
        $this->setModel($formTemplateItem);
    }
}