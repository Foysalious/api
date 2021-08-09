<?php namespace App\Sheba\Voucher;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Sheba\PosCustomerService\PosCustomerService;
use Exception;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class VoucherValidate
{
    /**
     * @var PosCustomerWrapper
     */
    private $posCustomerWrapper;
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
     * @var PosCustomerWrapper $posCustomer
     */
    private $posCustomer;

    public function __construct( PosCustomerWrapper $posCustomerWrapper, PosCustomerService $posCustomerService)
    {
        $this->posCustomerWrapper = $posCustomerWrapper;
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
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($this->amount);
        $pos_order_params = $pos_order_params->setApplicant( $this->posCustomer instanceof PosCustomer ? $this->posCustomer->getCustomer() :  new PosCustomer());
        $pos_order_params = $pos_order_params->setPartnerPosService($this->posServices);
        $result = voucher($this->code)->checkForPosOrder($pos_order_params);
        $result = $this->posCustomer instanceof PosCustomer ? $result->reveal() : $result->checkMobile($this->posCustomer->getCustomer()['mobile'])->reveal();

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
        if (!$this->partner->is_migration_completed)
            $pos_customer = $this->posCustomerId ? PosCustomer::find($this->posCustomerId) : new PosCustomer();
        else
            $pos_customer = $this->getPosCustomer();
        $this->posCustomer = $this->posCustomerWrapper->setCustomer($pos_customer);
    }

    private function getPosCustomer()
    {
        return $this->posCustomerService->setPartner($this->partner)->setCustomerId($this->posCustomerId)->getCustomerInfoFromSmanagerUserService();
    }
}
