<?php


namespace Sheba\NeoBanking\Banks;


use App\Models\Partner;
use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\NidInformation;
use Sheba\Dal\NeoBank\Model as NeoBank;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;
use Sheba\NeoBanking\PartnerNeoBankingInfo;
use Sheba\NeoBanking\Repositories\NeoBankRepository;

abstract class Bank
{
    public $id;
    public $name;
    public $code;
    public $logo;
    public $name_bn;
    /** @var NeoBankRepository $bankRepo */
    /**
     * @var NeoBank|null
     */
    protected $model;
    protected $bankRepo;
    /** @var Partner $partner */
    protected $partner;
    /** @var PartnerNeoBankingInfo $bankInfo */
    protected $bankInfo;

    /**
     * @param Partner $partner
     * @return Bank
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function __construct()
    {
        /** @var NeoBankRepository */
        $this->bankRepo = app(NeoBankRepository::class);
    }

    /**
     * @return Bank
     * @var |null
     */

    public function setBank(NeoBank $bank)
    {
        $this->model = $bank;
        if (!empty($bank)) {
            $this->mapBank();
        }
        return $this;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    function mapBank()
    {
        $this->id      = $this->model->id;
        $this->name    = $this->model->name;
        $this->name_bn = $this->model->name_bn;
        $this->logo    = $this->model->logo;
        $this->code    = $this->model->bank_code;
    }

    abstract public function categories(): BankFormCategoryList;

    abstract public function accountInfo(): BankAccountInfo;

    abstract public function categoryDetails(BankFormCategory $category): array;

    abstract public function homeInfo(): array;

    abstract public function completion(): BankCompletion;

    abstract public function accountDetailInfo(): BankAccountInfoWithTransaction;

    abstract public function getNidInfo($data): NidInformation;

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    public function loadInfo()
    {
        $this->bankInfo = (new PartnerNeoBankingInfo())->setPartner($this->partner);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankInfo()
    {
        return $this->bankInfo;
    }
}
