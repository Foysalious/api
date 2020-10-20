<?php namespace Sheba\NeoBanking;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use Sheba\Dal\NeoBank\Model as NeoBank;
use Sheba\NeoBanking\Banks\BankFactory;
use Sheba\NeoBanking\Banks\BankFormCategoryFactory;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Repositories\NeoBankRepository;

class NeoBanking
{
    /** @var NeoBank $bank */
    private $bank;
    private $partner;
    private $resource;
    private $post_data;

    public function __construct()
    {
    }


    /**
     * @param mixed $post_data
     * @return NeoBanking
     */
    public function setPostData($post_data)
    {
        $this->post_data = (array)json_decode($post_data, 0);
        return $this;
    }

    public function setBank($bank)
    {
        if (!($bank instanceof NeoBank)) $bank = (new NeoBankRepository())->getByCode($bank);
        $this->bank = $bank;
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return BankAccountInfoWithTransaction
     * @throws Exceptions\InvalidBankCode
     */
    public function accountDetails(): BankAccountInfoWithTransaction
    {
//        return [
//            'account_info' => [
//                'account_name'               => 'AL Amin Rahman',
//                'account_no'                 => '2441139',
//                'balance'                    => '4000',
//                'minimum_transaction_amount' => 1000,
//                'transaction_error_msg'      => 'ট্রান্সেকশন সফল হয়েছে'
//            ],
//            'transactions' => [
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '60000',
//                    'type'   => 'credit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '30000',
//                    'type'   => 'debit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '60000',
//                    'type'   => 'debit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '20000',
//                    'type'   => 'credit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '10000',
//                    'type'   => 'credit'
//                ],
//            ]
//        ];
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->accountDetailInfo();
    }

    public function createTransaction()
    {
        return [
            'status'  => 'success',
            'heading' => 'ট্রান্সেকশন সফল হয়েছে',
            'message' => 'ট্রান্সেকশন সফল হয়েছে'
        ];

    }

    /**
     * @return mixed
     * @throws Exceptions\InvalidBankCode
     */
    public function homepage()
    {
        return (new Home())->setPartner($this->partner)->get();
    }

    /**
     * @return Banks\BankCompletion
     * @throws Exceptions\InvalidBankCode
     */
    public function getCompletion()
    {
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->completion();

    }

    /**
     * @param $category_code
     * @return array
     * @throws Exceptions\InvalidBankCode
     * @throws Exceptions\InvalidBankFormCategoryCode
     */
    public function getCategoryDetail($category_code)
    {
        $bank = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
        return $bank->categoryDetails((new BankFormCategoryFactory())->setBank($bank)->getCategoryByCode($category_code))->toArray();

    }

    public function getNidInfo($data)
    {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getNidInfo($data);
    }

    public function getSDKLivelinessToken()
    {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getSDKLivelinessToken();
    }

    public function getGigatechKycStatus($data) {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getGigatechKycStatus($data);
    }

    /**
     * @param $category_code
     * @throws Exceptions\InvalidBankCode
     * @throws Exceptions\InvalidBankFormCategoryCode
     * @throws Exceptions\CategoryPostDataInvalidException
     */
    public function postCategoryDetail($category_code)
    {
        $bank     = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
        $category = (new BankFormCategoryFactory())->setBank($bank)->setPartner($this->partner)->getCategoryByCode($category_code);
        return $bank->loadInfo()->validateCategoryDetail($category, $this->post_data)->postCategoryDetail($category, $this->post_data);
    }

}
