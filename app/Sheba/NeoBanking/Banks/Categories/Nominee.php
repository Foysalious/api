<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\FormItemBuilder;
use Sheba\NeoBanking\Statics\FormStatics;

class Nominee extends BankFormCategory
{
    protected $code = 'nominee';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get(): CategoryGetter
    {
        $formItems = FormStatics::nominee();
        return $this->getFormData($formItems);
    }

    public function post($data)
    {
        return $this->last_updated;
    }

    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    public function getDummy()
    {
        // TODO: Implement getDummy() method.
    }
}
