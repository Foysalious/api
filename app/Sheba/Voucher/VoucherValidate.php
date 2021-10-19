<?php namespace App\Sheba\Voucher;

use App\Models\Partner;
use App\Models\PosCustomer as PosCustomerModel;
use App\Sheba\PosCustomerService\PosCustomerService;
use App\Sheba\UserMigration\Modules;
use Exception;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class VoucherValidate
{

    private $posCustomer;
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

    public function __construct(PosCustomer $posCustomer, PosCustomerService $posCustomerService)
    {
        $this->posCustomer = $posCustomer;
        $this->posCustomerService = $posCustomerService;
    }

    /**
     * @param $partnerId
     * @return VoucherValidate
     */
    public function setPartnerId($partnerId)
    {
        $this->partner = Partner::find($partnerId);
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
        $pos_order_params = $this->setPosOrderParams();
        $result = $this->reveal($pos_order_params);
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
        if (!$this->partner->isMigrated(Modules::POS)) {
            if (!$this->posCustomerId) $customer = (new PosCustomerModel());
            else $customer = PosCustomerModel::find($this->posCustomerId);
            $this->posCustomer
                ->setMobile(($profile = $customer->profile) ? $profile->mobile : null)
                ->setId($customer->id)
                ->setMovieTicketOrders($customer->movieTicketOrders)
                ->setProfile($customer->profile);
        } else {
            $customer = $this->getPosCustomer();
            $this->posCustomer
                ->setMobile($customer['mobile'])
                ->setId()
                ->setMovieTicketOrders(collect())
                ->setProfile();
        }
    }

    private function setPosOrderParams()
    {
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($this->amount);
        $pos_order_params = $pos_order_params->setApplicant($this->posCustomer);
        return $pos_order_params->setPartnerPosService($this->posServices);
    }

    private function reveal($pos_order_params)
    {
        $result = voucher($this->code)->checkForPosOrder($pos_order_params);
        $customer_mobile = $this->posCustomer->mobile;
        return $customer_mobile && $this->partner->isMigrated(Modules::POS) ? $result->checkMobile($customer_mobile)->reveal() : $result->reveal();
    }

    private function getPosCustomer()
    {
        return $this->posCustomerService->setPartner($this->partner)->setCustomerId($this->posCustomerId)->getCustomerInfoFromSmanagerUserService();
    }
}
