<?php namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
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
use Sheba\TPProxy\TPProxyServerError;

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

    public function accountInfo()
    {
        $account = $this->getAccount();
        if ($account) {
            $status = (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/status/'.$account);
            return $this->formatAccountData($status, $account);
        } else {
            return $this->formatEmptyData();
        }

    }

    public function categoryDetails(BankFormCategory $category): CategoryGetter
    {
        return $category->get();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function homeInfo()
    {
        return (new BankHomeInfo())->setBank($this)->setPartner($this->partner)->toArray();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function accountCreate()
    {
        return (new AccountCreate())->setBank($this)->setNaoBankingData($this->partner->neoBankInfo)->setPartner($this->partner)->setMobile($this->mobile)->makeData()->create()->store();
    }

    /**
     * @return BankCompletion
     * @throws InvalidBankCode
     * @throws InvalidListInsertion
     * @throws ReflectionException
     */
    public function completion(): BankCompletion
    {
        return (new Completion())->setBank($this)->setPartner($this->partner)->setMobile($this->mobile)->getAll();
    }

    private function getAccount()
    {
        $account = $this->partner->neoBankAccount()->where('bank_id',$this->id)->first();
        return !empty($account) ?$account->account_no:null;
    }

    public function accountDetailInfo()
    {
        return (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/balance/'.$this->getAccount());
    }

    public function transactionList()
    {
        return (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/transaction-list/'.$this->getAccount());
    }

    /**
     * @param $data
     * @return mixed
     * @throws TPProxyServerError
     */
    public function getNidInfo($data)
    {
        return (new PrimeBankClient())->setPartner($this->partner)->post('api/v1/nid-verification',$data);
    }

    /**
     * @return mixed
     * @throws TPProxyServerError
     */
    public function getSDKLivelinessToken()
    {
        return (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/liveliness-auth-token');
    }

    /**
     * @param $data
     * @return mixed
     * @throws TPProxyServerError
     */
    public function getGigatechKycStatus($data)
    {
        return (new PrimeBankClient())->setPartner($this->partner)->post('api/v1/kyc-status', $data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws TPProxyServerError
     */
    public function storeGigatechKyc($data)
    {
        return json_decode(json_encode((new PrimeBankClient())->setPartner($this->partner)->post('api/v1/kyc-submit', $data)),1);
    }

    public function formatAccountData($status, $account) {
        $data['has_account'] = 1;
        $data['applicant_name'] = $status->data->applicant_name;
        $data['account_no'] = $account;
        $accountStatus = $status->data->account_status;
        $data['account_status'] = $accountStatus;
        $formattedStatus = $this->formatStatus($accountStatus);
        $data['status_message'] = $formattedStatus['message'];
        $data['status_message_type'] = $formattedStatus['type'];

        return $data;
    }

    public function formatStatus($status) {
        $data = [];
        if($status->cpv === 'cpv_pending') {
            $data['message'] = config('neo_banking.cpv_pending_message');
            $data['type'] = config('neo_banking.message_type.cpv_pending');
        } else if($status->cpv === 'cpv_unverified') {
            $data['message'] = config('neo_banking.cpv_unverified_message');
            $data['type'] = config('neo_banking.message_type.cpv_unverified');
        } else if($status->cpv === 'cpv_verified') {
            if($status->sign === 'signed') {
                $data['message'] = config('neo_banking.signed_verified_message');
                $data['type'] = config('neo_banking.message_type.cpv_verified');;
            } else {
                $data['message'] = config('neo_banking.unsigned_message');
                $data['type'] = config('neo_banking.message_type.cpv_unsigned');
            }
        }
        return $data;
    }

    public function formatEmptyData()
    {
        $data['has_account'] = 0;
        $data['account_no'] = null;
        $data['account_status'] = null;
        $data['status_message'] = null;
        $data['status_message_type'] = null;

        return $data;
    }

}