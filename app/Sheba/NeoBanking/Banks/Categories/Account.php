<?php


namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\DTO\BankFormCategory;

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

    public function get()
    {
        return [];
    }

    public function post()
    {
        // TODO: Implement post() method.
    }

    public function getLastUpdated()
    {
        return $this->last_updated;
    }
}
