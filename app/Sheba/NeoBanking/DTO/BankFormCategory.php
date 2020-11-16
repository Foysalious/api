<?php


namespace Sheba\NeoBanking\DTO;


use Sheba\NeoBanking\Banks\Bank;
use Sheba\NeoBanking\Banks\BankCompletionDetail;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\PartnerNeoBankingInfo;
use Sheba\NeoBanking\Repositories\NeoBankAccountInformationRepository;
use Sheba\NeoBanking\Statics\BankStatics;

abstract class BankFormCategory
{
    protected $title;
    protected $data;
    protected $postData;
    protected $partner;
    protected $code;
    /** @var Bank $bank */
    protected $bank;
    protected $bankInfoRepo;
    protected $last_updated;
    /** @var PartnerNeoBankingInfo */
    protected $bankAccountData;
    protected $percentage;

    public function __construct()
    {
        $this->bankInfoRepo = (new NeoBankAccountInformationRepository());
        $this->setTitle(BankStatics::categoryTitles($this->code));
    }

    abstract public function completion();

    abstract public function get():CategoryGetter;

    abstract public function post($data);

    abstract public function getLastUpdated();

    abstract public function getDummy();

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param mixed $postData
     * @return BankFormCategory
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;
        return $this;
    }

    public function setLastUpdated()
    {
        $this->bank->loadInfo();
        $this->setBankAccountData($this->bank->getBankInfo());
        $category_data = $this->bankAccountData->getByCode($this->code);
        $this->last_updated = empty($category_data) ? '' : (isset($category_data->updated_at)) ? $category_data->updated_at : '';
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $partner
     * @return BankFormCategory
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Bank $bank
     * @return BankFormCategory
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    public function getCompletionDetails(): BankCompletionDetail
    {
        return (new BankCompletionDetail())->setTitle($this->getTitle())->setCode($this->code)->setLastUpdated($this->getLastUpdated())->setCompletionPercentage($this->completion());
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getFormData($formItems)
    {
        $data      = [];
        $formData  = $this->bankAccountData->getByCode($this->code);
        foreach ($formItems as $item) {
            $data[] = (new FormItemBuilder())->setData($formData)->build($item);
        }
        $this->setData($data);
        return (new CategoryGetter())->setCategory($this);
    }

    /**
     * @param PartnerNeoBankingInfo $bankAccountData
     * @return BankFormCategory
     */
    public function setBankAccountData($bankAccountData)
    {
        $this->bankAccountData = $bankAccountData;
        return $this;
    }

    public function getBengaliPercentage()
    {
        return convertNumbersToBangla($this->percentage, false);
    }
}
