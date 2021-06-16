<?php namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Carbon\Carbon;
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
use Sheba\NeoBanking\Exceptions\AccountCreateException;
use Sheba\NeoBanking\Exceptions\AccountNotFoundException;
use Sheba\NeoBanking\Exceptions\AccountNumberAlreadyExistException;
use Sheba\NeoBanking\Exceptions\InvalidBankCode;
use Sheba\NeoBanking\Exceptions\InvalidListInsertion;
use Sheba\NeoBanking\Statics\BankStatics;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;
use Sheba\TPProxy\TPProxyServerError;

class PrimeBank extends Bank
{

    private $apiClient;
    const CPV_PENDING_UNSIGNED    = "cpv_pending_unsigned";

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
            $headers = ['CLIENT-ID:'. config('neo_banking.sbs_client_id'), 'CLIENT-SECRET:'.  config('neo_banking.sbs_client_secret')];
            $status = (new PrimeBankClient())->setPartner($this->partner)->get("api/v1/client/account/$account/status", $headers);
            return $this->formatAccountData($status, $account);
        } else {
            if($this->hasAccountWithNullId()) {
                return $this->pendingAccountData($this->partner, $account);
            }
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
     * @param $account_no
     * @throws AccountNotFoundException
     * @throws AccountNumberAlreadyExistException
     */
    public function storeAccountNumber($account_no)
    {
        $neoBankAccount = $this->partner->neoBankAccount()->first();
        if(!isset($neoBankAccount)) throw new AccountNotFoundException();
        if($neoBankAccount->account_no !== null) throw new AccountNumberAlreadyExistException();
        $neoBankAccount->account_no = $account_no;
        $neoBankAccount->updated_at = Carbon::now();
        $neoBankAccount->updated_by = 0;
        $neoBankAccount->updated_by_name = "SBS - Prime Bank";
        $neoBankAccount->save();
        $this->sendNotification($account_no);
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
        $account = $this->partner->neoBankAccount()->where('bank_id', $this->id)->first();
        return !empty($account) ? $account->account_no : null;
    }

    private function hasAccountWithNullId()
    {
        $account = $this->partner->neoBankAccount()->where('bank_id', $this->id)->first();
        return ($account && $account->account_no == null);
    }

    private function getAccountDetails()
    {
        return $this->partner->neoBankAccount()->where('bank_id', $this->id)->first();
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
        $formattedStatus = NeoBankingGeneralStatics::formatStatus($accountStatus);
        $data['status_message'] = $formattedStatus['message'];
        $data['status_message_type'] = $formattedStatus['type'];

        return $data;
    }

    private function sendNotification($account_number)
    {
        $data = NeoBankingGeneralStatics::accountNumberUpdateData($account_number);
        NeoBankingGeneralStatics::sendPushNotification($this->partner, $data);
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

    public function pendingAccountData($status, $account) {
        $data['has_account'] = 1;
        $data['applicant_name'] = $status->name;
        $data['account_no'] = $account;
        $data['account_status'] = BankStatics::mapAccountFullStatus(self::CPV_PENDING_UNSIGNED);
        $data['status_message'] = config('neo_banking.cpv_pending_account_null_message');
        $data['status_message_type'] = "pending";
        return $data;
    }
}
