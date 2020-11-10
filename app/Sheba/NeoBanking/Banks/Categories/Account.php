<?php


namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Statics\FormStatics;

class Account extends BankFormCategory
{
    protected $code = 'account';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get() :CategoryGetter
    {
        $formItems = FormStatics::account();
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
        return [];
    }
}
