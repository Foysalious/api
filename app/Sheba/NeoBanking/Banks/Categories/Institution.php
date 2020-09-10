<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\BankCompletionDetail;
use Sheba\NeoBanking\DTO\BankFormCategory;

class Institution extends BankFormCategory
{
    protected $code = 'institution';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function post()
    {
        // TODO: Implement post() method.
    }

    public function getLastUpdated()
    {
        // TODO: Implement getLastUpdated() method.
    }
}
