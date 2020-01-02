<?php namespace Sheba\PartnerOrderRequest;

use App\Models\PartnerOrder;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\PartnerOrderRequest\Validators\CreateValidator;

class Creator
{
    /** @var PartnerOrderRequestRepositoryInterface $partnerOrderRequest */
    private $partnerOrderRequestRepo;
    /** @var CreateValidator $createValidator */
    private $createValidator;
    /** @var PartnerOrder $partnerOrder */
    private $partnerOrder;
    /** @var array $partnersId */
    private $partnersId;

    /**
     * Creator constructor.
     * @param PartnerOrderRequestRepositoryInterface $partner_order_request_repo
     * @param CreateValidator $create_validator
     */
    public function __construct(PartnerOrderRequestRepositoryInterface $partner_order_request_repo, CreateValidator $create_validator)
    {
        $this->partnerOrderRequestRepo = $partner_order_request_repo;
        $this->createValidator = $create_validator;
    }

    /**
     * @return array
     */
    public function hasError()
    {
        return $this->createValidator->hasError();
    }

    /**
     * @param PartnerOrder $partner_order
     * @return Creator
     */
    public function setPartnerOrder(PartnerOrder $partner_order)
    {
        $this->partnerOrder = $partner_order;
        return $this;
    }

    /**
     * @param array $partners_id
     * @return $this
     */
    public function setPartners(array $partners_id)
    {
        $this->partnersId = $partners_id;
        return $this;
    }

    public function create()
    {
        $data = [];
        foreach ($this->partnersId as $partner_id) {
            $data[] = [
                'partner_order_id' => $this->partnerOrder->id,
                'partner_id' => $partner_id
            ];
        }

        $this->partnerOrderRequestRepo->insert($data);
    }
}
