<?php namespace Sheba\Repositories\Business;

use App\Models\PurchaseRequestQuestion;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\PurchaseRequestQuestionRepositoryInterface;

class PurchaseRequestQuestionRepository extends BaseRepository implements PurchaseRequestQuestionRepositoryInterface
{
    public function __construct(PurchaseRequestQuestion $purchase_request_question)
    {
        parent::__construct();
        $this->setModel($purchase_request_question);
    }
}