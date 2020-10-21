<?php namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use ReflectionException;
use Sheba\NeoBanking\Banks\Bank;
use Sheba\NeoBanking\Banks\BankAccountInfo;
use Sheba\NeoBanking\Banks\BankCompletion;
use Sheba\NeoBanking\Banks\BankHomeInfo;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\Completion;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;
use Sheba\NeoBanking\Exceptions\InvalidBankCode;
use Sheba\NeoBanking\Exceptions\InvalidListInsertion;

class PrimeBank extends Bank
{

    private $apiClient;

    public function __construct()
    {
        parent::__construct();
        $this->apiClient = new ApiClient();
    }

    public function categories(): BankFormCategoryList
    {
        // TODO: Implement categories() method.
    }

    public function accountInfo(): BankAccountInfo
    {
        return $this->apiClient->setPartner($this->partner)->getAccountInfo();
    }

    public function categoryDetails(BankFormCategory $category): CategoryGetter
    {
        return $category->get();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function homeInfo(): array
    {
        return (new BankHomeInfo())->setBank($this)->setPartner($this->partner)->toArray();
    }

    /**
     * @return BankCompletion
     * @throws InvalidBankCode
     * @throws InvalidListInsertion
     * @throws ReflectionException
     */
    public function completion(): BankCompletion
    {
        return (new Completion())->setBank($this)->setPartner($this->partner)->getAll();
    }

    public function accountDetailInfo(): BankAccountInfoWithTransaction
    {
        return $this->apiClient->setPartner($this->partner)->getAccountDetailInfo();
    }

    public function getNidInfo($data)
    {
        return (new PrimeBankClient())->post('api/v1/nid-verification',$data);
    }

    public function getSDKLivelinessToken()
    {
        return (new PrimeBankClient())->get('api/v1/liveliness-auth-token');
    }

    public function getGigatechKycStatus($data)
    {
        return (new PrimeBankClient())->post('api/v1/kyc-status', $data);
    }

}