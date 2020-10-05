<?php


namespace Sheba\NeoBanking\DTO;


use Sheba\NeoBanking\Banks\Bank;
use Sheba\NeoBanking\Banks\BankCompletionDetail;
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
    protected $last_updated = 'today';

    public function __construct()
    {
        $this->bankInfoRepo = (new NeoBankAccountInformationRepository());
        $this->setTitle(BankStatics::categoryTitles($this->code));
    }

    abstract public function completion();

    abstract public function get();

    abstract public function post();

    abstract public function getLastUpdated();

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
}
