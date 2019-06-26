<?php namespace Sheba\Repositories\Business;

use App\Models\ProcurementQuestion;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;

class ProcurementQuestionRepository extends BaseRepository implements ProcurementQuestionRepositoryInterface
{
    public function __construct(ProcurementQuestion $procurement_question)
    {
        parent::__construct();
        $this->setModel($procurement_question);
    }
}