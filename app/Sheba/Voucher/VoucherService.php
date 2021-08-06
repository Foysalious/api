<?php namespace App\Sheba\Voucher;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Sheba\PosCustomerService\PosCustomerService;
use Exception;

class VoucherService
{

    /**
     * @var VoucherValidate
     */
    private $voucherValidate;
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



    public function __construct(VoucherValidate $voucherValidate, PosCustomerWrapper $posCustomerWrapper, PosCustomerService $posCustomerService)
    {

        $this->voucherValidate = $voucherValidate;
        $this->posCustomerWrapper = $posCustomerWrapper;
        $this->posCustomerService = $posCustomerService;
    }

    /**
     * @param mixed $partnerId
     * @return VoucherService
     */
    public function setPartner($partnerId)
    {
        $this->partner = Partner::find($partnerId);
        return $this;
    }

    /**
     * @param mixed $amount
     * @return VoucherService
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $posServices
     * @return VoucherService
     */
    public function setPosServices($posServices)
    {
        $this->posServices = $posServices;
        return $this;
    }

    /**
     * @param mixed $code
     * @return VoucherService
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
    /**
     * @throws Exception
     */
    public function validate()
    {
        if (!$this->partner->is_migration_completed)
            $pos_customer = $this->posCustomerId ? PosCustomer::find($this->posCustomerId) : new PosCustomer();
        else $pos_customer = $this->getPosCustomer();
        $posCustomer = $this->posCustomerWrapper->setCustomer($pos_customer);
        return $this->voucherValidate->setPosCustomer($posCustomer)->setAmount($this->amount)->setCode($this->code)
            ->setPosServices($this->posServices)->validate();
    }

    public function getPosCustomer()
    {
        return $this->posCustomerService->setPartner($this->partner)->setCustomerId($this->posCustomerId)->getCustomerInfoFromSmanagerUserService();
    }

    /**
     * @param $posCustomerId
     * @return VoucherService
     */
    public function setPosCustomerId($posCustomerId)
    {
        $this->posCustomerId = $posCustomerId;
        return $this;
    }
}