<?php namespace App\Sheba\Voucher;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Sheba\PosCustomerService\PosCustomerService;
use Exception;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class VoucherValidate
{
    /**
     * @var PosCustomerInfo
     */
    private $posCustomerInfo;
    /**
     * @var PosCustomerService
     */
    private $posCustomerService;
    /**
     * @var $partner Partner
     */
    private $partner;
    protected $amount;
    protected $posServices;
    protected $code;
    private $posCustomerId;
    /**
     * @var PosCustomer
     */
    private $posCustomer;



    public function __construct(PosCustomerInfo $posCustomerInfo, PosCustomerService $posCustomerService)
    {
        $this->posCustomerInfo = $posCustomerInfo;
        $this->posCustomerService = $posCustomerService;
    }


    /**
     * @param $partner
     * @return VoucherValidate
     */
    public function setPartner($partner)
    {
        if($partner instanceof Partner)
            $this->partner = $partner;
        else
            $this->partner = Partner::find($partner);

        return $this;
    }

    /**
     * @param mixed $amount
     * @return VoucherValidate
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $posServices
     * @return VoucherValidate
     */
    public function setPosServices($posServices)
    {
        $this->posServices = $posServices;
        return $this;
    }

    /**
     * @param mixed $code
     * @return VoucherValidate
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param $posCustomerId
     * @return VoucherValidate
     */
    public function setPosCustomerId($posCustomerId)
    {
        $this->posCustomerId = $posCustomerId;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        $this->resolvePosCustomer();
        if ($this->posCustomerId && !$this->partner->is_migration_completed)
            $this->posCustomerInfo->setCustomerMobile($this->posCustomer->mobile);
        else
            $this->posCustomerInfo->setCustomerMobile($this->getPosCustomer()['mobile']);
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($this->amount);
        $pos_order_params = $pos_order_params->setApplicant($this->posCustomerInfo->getCustomer());
        $pos_order_params = $pos_order_params->setPartnerPosService($this->posServices);
        $result = voucher($this->code)->checkForPosOrder($pos_order_params);
        $result =  $result->checkMobile($this->posCustomerInfo->getCustomerMobile())->reveal();

        $response = [];
        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $response = [
                'amount' => (double)$result['amount'],
                'code' => $voucher->code,
                'id' => $voucher->id,
                'title' => $voucher->title
            ];
        }
        return $response;
    }

    private function resolvePosCustomer()
    {
        $this->posCustomer = $this->posCustomerId ? PosCustomer::find($this->posCustomerId) : new PosCustomer();
        $this->posCustomerInfo = $this->posCustomerInfo->setCustomer($this->posCustomer);
    }

    private function getPosCustomer()
    {
        return $this->posCustomerService->setPartner($this->partner)->setCustomerId($this->posCustomerId)->getCustomerInfoFromSmanagerUserService();
    }
}
