<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\CompletionCalculation;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Statics\FormStatics;

class Nominee extends BankFormCategory
{
    protected $code = 'nominee';

    public function completion()
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function get(): CategoryGetter
    {
        $formItems = FormStatics::nominee();
        return $this->getFormData($formItems);
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
        if (!isset($this->data)) {
            $formItems = FormStatics::nominee();
            $this->bank->loadInfo();
            $this->setBankAccountData($this->bank->getBankInfo());
            $this->getFormData($formItems);
        }
        $this->percentage = (new CompletionCalculation())->get($this->data);
        $this->percentage = round($this->percentage);
        return $this->percentage;
    }
}
