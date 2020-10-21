<?php namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\Partner;
use Carbon\Carbon;
use Exception;
use Sheba\Dal\TopUpBlacklistNumber\Contract;
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;
use Event;

class TopUpRequest
{
    const MINIMUM_INTERVAL_BETWEEN_TWO_TOPUP_IN_SECOND = 10;

    private $mobile;
    private $amount;
    private $type;
    /** @var TopUpAgent */
    private $agent;
    private $vendorId;
    /** @var Vendor */
    private $vendor;
    private $vendorFactory;
    private $errorMessage;
    private $name;
    private $bulk_id;
    private $from_robi_topup_wallet;
    private $topUpBlockNumberRepository;

    public function __construct(VendorFactory $vendor_factory, Contract $top_up_block_number_repository)
    {
        $this->vendorFactory = $vendor_factory;
        $this->topUpBlockNumberRepository = $top_up_block_number_repository;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return TopUpRequest
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getAgent()
    {
        return $this->agent;
    }

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param $vendor_id
     * @return $this
     * @throws Exception
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        $this->vendor = $this->vendorFactory->getById($this->vendorId);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return (double)$this->amount;
    }

    /**
     * @param mixed $amount
     * @return TopUpRequest
     */
    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return TopUpRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = formatMobile($mobile);
        return $this;
    }

    /**
     * @param $from_robi_topup_wallet
     * @return TopUpRequest
     */
    public function setRobiTopupWallet($from_robi_topup_wallet)
    {
        $this->from_robi_topup_wallet = $from_robi_topup_wallet;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRobiTopupWallet()
    {
        return $this->from_robi_topup_wallet;
    }

    /**
     * @return Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @return mixed
     */
    public function getOriginalMobile()
    {
        return getOriginalMobileNumber($this->mobile);
    }

    public function hasError()
    {
        if ($this->agent instanceof Partner && !$this->agent->isNIDVerified()) {
            $this->errorMessage = "You are not verified to do this operation.";
            return 1;
        } else if ($this->agent instanceof Affiliate && $this->agent->isNotVerified()) {
            $this->errorMessage = "You are not verified to do this operation.";
            return 1;
        }
        if ($this->topUpBlockNumberRepository->findByMobile($this->mobile)) {
            Event::fire(new TopUpRequestOfBlockedNumber($this->agent,$this->mobile));
            $this->errorMessage = "You can't recharge to a blocked number.";
            return 1;
        }
        if ($this->from_robi_topup_wallet == 1 && $this->agent->robi_topup_wallet < $this->amount) {
            $this->errorMessage = "You don't have sufficient balance to recharge.";
            return 1;
        }
        if ($this->from_robi_topup_wallet != 1 && $this->agent->wallet < $this->amount) {
            $this->errorMessage = "You don't have sufficient balance to recharge.";
            return 1;
        }
        if (!$this->vendor->isPublished()) {
            $this->errorMessage = "Sorry, we don't support this operator at this moment.";
            return 1;
        }

        return 0;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return TopUpRequest
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBulkId()
    {
        return $this->bulk_id;
    }

    /**
     * @param mixed $bulk_id
     * @return TopUpRequest
     */
    public function setBulkId($bulk_id)
    {
        $this->bulk_id = $bulk_id;
        return $this;
    }

    private function hasLastTopupWithinIntervalTime()
    {
        $last_topup = $this->agent->topups()->select('id', 'created_at')->orderBy('id', 'desc')->first();
        return $last_topup && $last_topup->created_at->diffInSeconds(Carbon::now()) < self::MINIMUM_INTERVAL_BETWEEN_TWO_TOPUP_IN_SECOND;
    }
}
