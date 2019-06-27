<?php namespace Sheba\Business\Purchase;

use App\Models\PurchaseRequest;
use Exception;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\PurchaseRequestRepositoryInterface;

class StatusChanger
{
    use ModificationFields;

    /** @var PurchaseRequestRepositoryInterface $purchaseRequestRepository */
    private $purchaseRequestRepository;
    /** @var array $statuses */
    private $statuses;
    /** @var PurchaseRequest $purchaseRequest */
    private $purchaseRequest;
    /** @var array $data */
    private $data;

    public function __construct(PurchaseRequestRepositoryInterface $purchase_request_repo)
    {
        $this->purchaseRequestRepository = $purchase_request_repo;
        $this->statuses = config('b2b.PURCHASE_REQUEST_STATUS');
    }

    public function hasError()
    {
        if (!in_array($this->data['status'], $this->statuses)) return "Invalid Status!";
        return false;
    }

    public function setPurchaseRequest(PurchaseRequest $purchase_request)
    {
        $this->purchaseRequest = $purchase_request;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     *
     * @throws Exception
     */
    public function change()
    {
        $data = ['status' => $this->data['status']];
        if ($this->data['status'] == $this->statuses['rejected']) {
            $data['rejection_note'] = $this->data['rejection_note'];
        }
        $this->purchaseRequestRepository->update($this->purchaseRequest, $this->withUpdateModificationField($data));
    }
}