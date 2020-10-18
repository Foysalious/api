<?php


namespace Sheba\NeoBanking\Banks;


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

    private function mapBank()
    {
        $this->id      = $this->model->id;
        $this->name    = $this->model->name;
        $this->name_bn = $this->model->name_bn;
        $this->logo    = $this->model->logo;
        $this->code    = $this->model->bank_code;
    }

    abstract public function categories(): BankFormCategoryList;

    abstract public function accountInfo(): BankAccountInfo;

    abstract public function categoryDetails(BankFormCategory $category): CategoryGetter;

    abstract public function homeInfo(): array;

    abstract public function completion(): BankCompletion;

    abstract public function accountDetailInfo(): BankAccountInfoWithTransaction;

    public function postCategoryDetail(BankFormCategory $category, $data)
    {
        return $category->post($data);
    }

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
            if ($item['field_type'] == 'multiView') {
                $this->validatePostData($item['views'], $data[$item['name']]);
                continue;
            }
            if ($item['mandatory'] && (!isset($data[$item['name']]) || empty($data[$item['name']]))) {
                throw new CategoryPostDataInvalidException($item['error_message']);
            }
            if ($item['field_type'] == 'date') {
                try {
                    Carbon::parse($data[$item['name']]);
                } catch (\Throwable $e) {
                    throw new CategoryPostDataInvalidException("Date is Invalid");
                }
            };
        }
    }
}
