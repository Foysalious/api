<?php namespace Sheba\NeoBanking;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use Sheba\Dal\NeoBank\Model as NeoBank;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
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
    private $gigatechKycData;
    private $mobile;

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

    public function setGigatechKycData($gigatechKycData)
    {
        $this->gigatechKycData = $gigatechKycData;
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

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return BankAccountInfoWithTransaction
     * @throws Exceptions\InvalidBankCode
     */
    public function accountDetails()
    {
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->accountDetailInfo();
    }

    public function transactionList()
    {
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->transactionList();
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
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->setMobile($this->mobile)->get()->completion();

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

    /**
     * @throws Exceptions\InvalidBankCode
     */
    public function storeAccount()
    {
        $bank = (new BankFactory())->setPartner($this->partner)->setMobile($this->mobile)->setBank($this->bank)->get();
        return $bank->accountCreate();
    }

    /**
     * @param $data
     * @return mixed
     * @throws Exceptions\InvalidBankCode
     */
    public function getNidInfo($data)
    {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getNidInfo($data);
    }

    /**
     * @return mixed
     * @throws Exceptions\InvalidBankCode
     */
    public function getSDKLivelinessToken()
    {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getSDKLivelinessToken();
    }

    /**
     * @param $data
     * @return mixed
     * @throws Exceptions\InvalidBankCode
     */
    public function getGigatechKycStatus($data) {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        return $bank->getGigatechKycStatus($data);
    }
    public function uploadDocument($file, $key){
        $doc=(new NeoBankingFileHandler())->setPartner($this->partner)->uploadDocument($file,$key);
        $this->setPostData(json_encode([$key => $doc->getUploadedUrl()]));
        return $this;
    }

    /**
     * @return mixed
     * @throws Exceptions\CategoryPostDataInvalidException
     * @throws Exceptions\InvalidBankCode
     * @throws Exceptions\InvalidBankFormCategoryCode
     */
    public function storeGigatechKyc() {
        $bank = (new BankFactory())->setBank($this->bank)->get();
        $response = (array)$bank->storeGigatechKyc($this->gigatechKycData);
        $handler= (new NeoBankingFileHandler())->setPartner($this->partner);
        if(4002 === $response['data']["status_code"] || 4003 === $response['data']["status_code"]) {
            $nid_front =$handler->getImageUrl($this->gigatechKycData['id_front'], "nid_front");
            $nid_back = $handler->getImageUrl($this->gigatechKycData['id_back'], "nid_back");
            $applicant_photo = $handler->getImageUrl($this->gigatechKycData['applicant_photo'], "applicant_photo");
            $data = array_except($this->gigatechKycData, ["is_kyc_store","remember_token","applicant_photo","id_front","id_back"]);
            $data = array_merge($data, ['nid_front'=>$nid_front, 'nid_back'=>$nid_back, 'applicant_photo' =>$applicant_photo]);
            $this->setPostData( json_encode($data))->postCategoryDetail('nid_selfie');
        }
        return $response;
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
