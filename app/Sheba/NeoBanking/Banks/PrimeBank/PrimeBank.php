<?php namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Carbon\Carbon;
use Exception;
use ReflectionException;
use Sheba\NeoBanking\Banks\Bank;
use Sheba\NeoBanking\Banks\BankCompletion;
use Sheba\NeoBanking\Banks\BankHomeInfo;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\Completion;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;
use Sheba\NeoBanking\Exceptions\AccountNotFoundException;
use Sheba\NeoBanking\Exceptions\AccountNumberAlreadyExistException;
use Sheba\NeoBanking\Statics\BankStatics;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;
use Sheba\TPProxy\TPProxyServerError;

class PrimeBank extends Bank
{

    private $apiClient;
    private $partnerNeoBankingAccount;
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

    /**
     * @return array
     * @throws AccountNotFoundException
     * @throws TPProxyServerError
     */
    public function accountInfo(): array
    {
        $transactionId = $this->getTransactionId();
        if(!$transactionId) throw new AccountNotFoundException();

        $headers = ['CLIENT-ID:'. config('neo_banking.sbs_client_id'), 'CLIENT-SECRET:'.  config('neo_banking.sbs_client_secret')];
        $status = (new PrimeBankClient())->setPartner($this->partner)->get("api/v1/client/account/$transactionId/status", $headers);
        return $this->formatAccountData($status, $transactionId);

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
     */
    public function accountNumberNotification($account_no)
    {
        $neoBankAccount = $this->partner->neoBankAccount()->first();
        if(!isset($neoBankAccount)) throw new AccountNotFoundException();
        $this->sendNotification($account_no);
    }

    /**
     * @return BankCompletion
     */
    public function completion(): BankCompletion
    {
        return (new Completion())->setBank($this)->setPartner($this->partner)->setMobile($this->mobile)->getAll();
    }

    private function getAccount()
    {
        $this->partnerNeoBankingAccount = $this->partner->neoBankAccount()->where('bank_id', $this->id)->first();
        return !empty($this->partnerNeoBankingAccount) ? $this->partnerNeoBankingAccount->account_no : null;
    }

    private function getTransactionId()
    {
        if (!isset($this->partnerNeoBankingAccount)){
            $this->partnerNeoBankingAccount = $this->partner->neoBankAccount()->where('bank_id', $this->id)->first();;
        }

        return !empty($this->partnerNeoBankingAccount) ? $this->partnerNeoBankingAccount->transaction_id : null;
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
        $headers=['CLIENT-ID:'. config('neo_banking.sbs_client_id'), 'CLIENT-SECRET:'.  config('neo_banking.sbs_client_secret')];
        return (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/client/accounts/'.$this->getTransactionId().'/balance', $headers);
    }

    public function transactionList()
    {
        $headers=['CLIENT-ID:'. config('neo_banking.sbs_client_id'), 'CLIENT-SECRET:'.  config('neo_banking.sbs_client_secret')];
        return (new PrimeBankClient())->setPartner($this->partner)->get('api/v1/client/accounts/'.$this->getTransactionId().'/transaction-list', $headers);
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

    /**
     * @return array|string[]
     */
    public function getAcknowledgment()
    {
        $term_condition = BankStatics::PblTermsAndCondition();
        $data = [
            'body' => '<b>১.</b> আমি যে ব্যবসার জন্য প্রাইম ব্যাংক লিমিটেডে অ্যাকাউন্ট খোলার জন্য আবেদন করছি তার "একমাত্র-স্বত্বাধিকারী",<br> আমি নিজে ।<br> <b>২.</b> এই আবেদনপত্রটির সাথে প্রদত্ত সমস্ত তথ্য সত্য।<br> <b>৩.</b> আমি ব্যাংকের চাহিদামাফিক প্রয়োজনীয় তথ্য/নথি সরবরাহ করব।<br> <b>৪.</b> আমি ব্যাংকে যে তথ্য সরবরাহ করেছি তাতে যদি কোনো পরিবর্তন ঘটে তবে আমি ৩০ (ত্রিশ) ক্যালেন্ডার দিনের<br> মধ্যে ব্যাংকে অবহিত করার উদ্যোগ নিবো।<br> <b>৫.</b> আমি প্রাইম ব্যাংক লিমিটেড এর এসএমএস পরিষেবাতে নিবন্ধন করতে সম্মত হয়েছি যার জন্য অমি আমার ঘোষিত মোবাইল<br> নম্বরে ব্যাংক লেনদেনের তথ্য সহ অন্যান্য সকল তথ্য পেতে আগ্রহী।<br> <b>৬.</b> আমি সম্মতি প্রকাশ করছি যে , যে শর্তাবলী ব্যাংকের বিবেচনার ভিত্তিতে পর্যালোচনা এবং পরিবর্তন যোগ্য; এবং<br> কোনো পরিবর্তন যদি হয় তা গ্রাহকের ক্ষেত্রে ও সমানভাবে প্রযোজ্য হবে । উপরে উল্লিখিত পাঠ্যের বাংলা ও ইংরেজী<br> সংস্করণের মধ্যে যদি কোনো বিভ্রান্তি দেখা দেয় তবে ইংরেজি সংস্করণটি সঠিক হিসাবে গণ্য হবে ।',
            'number_of_acknowledgements' => 6,
            'agreement' => 'আমি প্রাইম  ব্যাংকের <b><u>শর্তে</u></b> রাজি।',
            'pbl_terms_and_condition_link' => $term_condition
            ];
        try {
            return  $data;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param $status
     * @param $transactionId
     * @return array
     */
    public function formatAccountData($status, $transactionId): array
    {
        $data['has_account'] = 1;
        $data['applicant_name'] = $status->data->applicant_name;
        $data['account_no'] = $status->data->account;
        $data['transaction_id'] = (string)$transactionId;
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
        $data['transaction_id'] = null;
        $data['account_status'] = null;
        $data['status_message'] = null;
        $data['status_message_type'] = null;

        return $data;
    }

    /**
     * @param $status
     * @param $transaction_id
     * @return array
     */
    public function pendingAccountData($status, $transaction_id): array
    {
        $data['has_account'] = 1;
        $data['applicant_name'] = $status->name;
        $data['account_no'] = null;
        $data['transaction_id'] = $transaction_id;
        $data['account_status'] = BankStatics::mapAccountFullStatus(self::CPV_PENDING_UNSIGNED);
        $data['status_message'] = config('neo_banking.cpv_pending_account_null_message');
        $data['status_message_type'] = "pending";
        return $data;
    }
}
