<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\DTO\BankFormCategory;

class Personal extends BankFormCategory
{
    protected $code = 'personal';

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
        return $this->last_updated;
    }
}
