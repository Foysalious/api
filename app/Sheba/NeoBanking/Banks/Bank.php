<?php namespace Sheba\NeoBanking\Banks;


use App\Models\Partner;
use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use Carbon\Carbon;
use App\Sheba\NeoBanking\Banks\NidInformation;
use Sheba\Dal\NeoBank\Model as NeoBank;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;
use Sheba\NeoBanking\Exceptions\CategoryPostDataInvalidException;
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
    protected $mobile;

    /**
     * @param Partner $partner
     * @return Bank
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
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

    private function mapBank()
    {
        $this->id      = $this->model->id;
        $this->name    = $this->model->name;
        $this->name_bn = $this->model->name_bn;
        $this->logo    = $this->model->logo;
        $this->code    = $this->model->bank_code;
    }

    abstract public function categories(): BankFormCategoryList;

    abstract public function accountInfo();

    abstract public function categoryDetails(BankFormCategory $category): CategoryGetter;

    abstract public function homeInfo();

    abstract public function accountCreate();

    abstract public function storeAccountNumber($account_no);

    abstract public function completion(): BankCompletion;

    abstract public function accountDetailInfo();

    abstract public function transactionList();

    public function postCategoryDetail(BankFormCategory $category, $data)
    {
        return $category->post($data);
    }

    abstract public function getNidInfo($data);

    abstract public function getSDKLivelinessToken();

    abstract public function getGigatechKycStatus($data);

    abstract public function storeGigatechKyc($data);

    abstract public function getAcknowledgment();

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

    /**
     * @param $category
     * @param $post_data
     * @return $this
     * @throws CategoryPostDataInvalidException
     */
    public function validateCategoryDetail($category, $post_data)
    {
        $detail = $this->categoryDetails($category);
        $this->validatePostData($detail->getFormItems(), $post_data);
        return $this;
    }

    /**
     * @param $detail
     * @param $post_data
     * @throws CategoryPostDataInvalidException
     */
    private function validatePostData($detail, $post_data)
    {
        $data = (array)$post_data;
        foreach ($detail as $key => $item) {
            $hasValue = false;
            if ($item['field_type'] == 'multipleView') {
                $this->validatePostData($item['views'], $data[$item['name']]);
                continue;
            }

            if ($item['mandatory']) {
                if ((!isset($data[$item['name']]) || empty($data[$item['name']]))) throw new CategoryPostDataInvalidException($item['error_message']);
                if ($item['field_type'] == 'radioGroup' || $item['field_type'] == 'conditionalSelect') {
                    foreach ($data[$item['name']] as $key => $value) {
                        if (($item['field_type'] == 'radioGroup' && $value == 1) || ($item['field_type'] == 'conditionalSelect' && $value !== '')) $hasValue = true;
                    }
                    if (!$hasValue) throw new CategoryPostDataInvalidException($item['error_message']);
                }
            }
            if ($item['field_type'] == 'date') {
                try {
                    if(isset($data[$item['name']]))
                        Carbon::parse($data[$item['name']]);
                } catch (\Throwable $e) {
                    throw new CategoryPostDataInvalidException("Date is Invalid");
                }
            };
        }
    }
}
