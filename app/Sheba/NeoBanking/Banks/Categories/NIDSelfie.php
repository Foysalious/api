<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\CompletionCalculation;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Statics\FormStatics;

class NIDSelfie extends BankFormCategory
{
    protected $code = 'nid_selfie';

    public function __construct()
    {
        parent::__construct();
    }

    public function get(): CategoryGetter
    {
        $formItems = FormStatics::nidSelfie();
        return $this->getFormData($formItems);
    }

    public function completion()
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function post($data)
    {
        return !!$this->bankAccountData->postByCode($this->code, $data);
    }

    public function getLastUpdated()
    {
        $this->setLastUpdated();
        return $this->last_updated;
    }

    public function getDummy()
    {
        // TODO: Implement getDummy() method.
    }

    public function percentageCalculation()
    {
        $this->bank->loadInfo();
        $this->setBankAccountData($this->bank->getBankInfo());
        $this->percentage = (empty($this->bankAccountData->getByCode($this->code))) ? 0 : 100;
        return $this->percentage;
    }
}
